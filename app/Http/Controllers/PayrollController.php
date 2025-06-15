<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendence;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;

class PayrollController extends Controller
{
    // Show payroll data for specific employee and month
    public function show(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $payroll = Payroll::with('employee.department')->where('employee_id', $request->employee_id)
            ->where('month', $request->month)
            ->first();

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll data not found for this employee and month.'
            ], 404);
        }

        // Return payroll data with employee full name, department, and profile picture
        return response()->json([
            'success' => true,
            'data' => [
                'payroll' => $payroll,
                'employee_full_name' => $payroll->employee->first_name . ' ' . $payroll->employee->last_name,
                'department_name' => optional($payroll->employee->department)->name,
                'profile_picture' => $payroll->employee->profile_picture
            ]
        ]);
    }
    // Get all payroll records for all employees
public function allPayrolls()
{
    $payrolls = Payroll::with('employee.department')->get();

    $data = $payrolls->map(function ($payroll) {
        return [
            'payroll' => $payroll,
            'employee_full_name' => $payroll->employee->first_name . ' ' . $payroll->employee->last_name,
            'department_name' => optional($payroll->employee->department)->name,
            'profile_picture' => $payroll->employee->profile_picture
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}


    // Get payroll list with filters
    public function summary(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'employee_name' => 'nullable|string',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        $payrolls = Payroll::with('employee.department')
            ->when($request->month, fn($q) => $q->where('month', $request->month))
            ->when($request->employee_name, function ($q) use ($request) {
                $q->whereHas('employee', function ($sub) use ($request) {
                    $sub->where('first_name', 'like', "%{$request->employee_name}%")
                        ->orWhere('last_name', 'like', "%{$request->employee_name}%");
                });
            })
            ->when($request->start_date && $request->end_date, fn($q) => $q->whereBetween('created_at', [$request->start_date, $request->end_date]))
            ->get();

        // Format response with full name, department, and profile picture
        $data = $payrolls->map(function ($payroll) {
            return [
                'payroll' => $payroll,
                'employee_full_name' => $payroll->employee->first_name . ' ' . $payroll->employee->last_name,
                'department_name' => optional($payroll->employee->department)->name,
                'profile_picture' => $payroll->employee->profile_picture
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // Recalculate payroll for specific employee and month
    public function recalculate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $employee = Employee::with('generalSetting')->find($request->employee_id);
        $generalSettings = $employee->generalSetting;

        if (!$generalSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Employee settings not found.'
            ], 400);
        }

        $monthStart = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $weekend_days = $generalSettings->weekend_days ?? ['Saturday', 'Sunday'];
        $officialHolidays = Holiday::whereBetween('date', [$monthStart, $monthEnd])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $businessDays = collect(CarbonPeriod::create($monthStart, $monthEnd))
            ->reject(fn($date) => in_array($date->format('l'), $weekend_days) || in_array($date->toDateString(), $officialHolidays))
            ->count();

        $attendances = Attendence::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $attendedDays = $attendances->unique('date')->count();
        $absentDays = $businessDays - $attendedDays;
        $totalLate = $attendances->sum(fn($a) => max(0, $a->lateDurationInHours ?? 0));
        $totalOvertime = $attendances->sum(fn($a) => max(0, $a->overtimeDurationInHours ?? 0));

        $fixedMonthDays = 30;
        $daily_rate = $employee->salary / $fixedMonthDays;
        $salaryPerHour = $employee->working_hours_per_day > 0 ? $daily_rate / $employee->working_hours_per_day : 0;

        $late_deduction_amount = $this->round2($this->calculateDeduction($totalLate, $generalSettings->deduction_type, $generalSettings->deduction_value, $salaryPerHour));
        $absence_deduction_amount = $this->round2($absentDays * $daily_rate);
        $overtime_value = $this->round2($this->calculateOvertime($totalOvertime, $generalSettings->overtime_type, $generalSettings->overtime_value, $salaryPerHour));

        $total_deduction_amount = $this->round2($absence_deduction_amount + $late_deduction_amount);
        $net_salary = $this->round2(max(0, $employee->salary - $total_deduction_amount + $overtime_value));

        $payroll = Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $request->month,
            ],
            [
                'month_days' => $businessDays,
                'attended_days' => $attendedDays,
                'absent_days' => $absentDays,
                'total_overtime' => $totalOvertime,
                'total_bonus_amount' => $overtime_value,
                'total_late_hours' => $totalLate,
                'total_deduction_amount' => $total_deduction_amount,
                'net_salary' => $net_salary,
                'absence_deduction_amount' => $absence_deduction_amount,
                'late_deduction_amount' => $late_deduction_amount,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $payroll
        ]);
    }

    // Calculate late deductions
    private function calculateDeduction($totalLate, $deduction_type, $deduction_value, $salaryPerHour)
    {
        if ($deduction_type == 'money') {
            return $totalLate * $deduction_value;
        }
        return $totalLate * $deduction_value * $salaryPerHour;
    }

    // Calculate overtime bonus
    private function calculateOvertime($totalOvertime, $overtime_type, $overtime_value, $salaryPerHour)
    {
        if ($overtime_type == 'money') {
            return $totalOvertime * $overtime_value;
        }
        return $totalOvertime * $overtime_value * $salaryPerHour;
    }

    // Round values to 2 decimal places
    private function round2($value)
    {
        return bcadd($value, '0', 2);
    }

}
