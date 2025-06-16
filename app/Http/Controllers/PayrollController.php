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
    public function allEmployeesData()
    {
        $employees = Employee::with(['department', 'generalSetting', 'latestPayroll'])->get();
        

        $data = $employees->map(function ($employee) {
            $payroll = $employee->latestPayroll;

            return [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'full_name' => "{$employee->first_name} {$employee->last_name}",
                'email' => $employee->email,
                'salary' => $employee->salary,
                'working_hours_per_day' => $employee->working_hours_per_day,
               'profile_image_url' => $employee->profileImageUrl,


               'dep_name' => optional($employee->department)->dept_name,

                'general_settings' => [
                    'weekend_days' => $employee->generalSetting->weekend_days ?? [],
                    'deduction_type' => $employee->generalSetting->deduction_type,
                    'deduction_value' => $employee->generalSetting->deduction_value,
                    'overtime_type' => $employee->generalSetting->overtime_type,
                    'overtime_value' => $employee->generalSetting->overtime_value,
                ],
                'payroll' => $payroll ? [
                    'month' => $payroll->month,
                    'month_days' => $payroll->month_days,
                    'attended_days' => $payroll->attended_days,
                    'absent_days' => $payroll->absent_days,
                    'total_overtime' => $payroll->total_overtime,
                    'total_bonus_amount' => $payroll->total_bonus_amount,
                    'total_late_hours' => $payroll->total_late_hours,
                    'total_deduction_amount' => $payroll->total_deduction_amount,
                    'net_salary' => $payroll->net_salary,
                    'absence_deduction_amount' => $payroll->absence_deduction_amount,
                    'late_deduction_amount' => $payroll->late_deduction_amount,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

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

        $data = $payrolls->map(function ($payroll) {
            return [
                'employee_full_name' => "{$payroll->employee->first_name} {$payroll->employee->last_name}",
                'month' => $payroll->month,
                'salary' => $payroll->employee->salary,
                'month_days' => $payroll->month_days,
                'attended_days' => $payroll->attended_days,
                'absent_days' => $payroll->absent_days,
                'total_overtime' => $payroll->total_overtime,
                'total_bonus_amount' => $payroll->total_bonus_amount,
                'total_late_hours' => $payroll->total_late_hours,
                'total_deduction_amount' => $payroll->total_deduction_amount,
                'net_salary' => $payroll->net_salary,
                'absence_deduction_amount' => $payroll->absence_deduction_amount,
                'late_deduction_amount' => $payroll->late_deduction_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // باقي دوالك زي recalculate موجودة فعلاً بدون تعديل...
    
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

    private function calculateDeduction($totalLate, $deduction_type, $deduction_value, $salaryPerHour)
    {
        if ($deduction_type == 'money') {
            return $totalLate * $deduction_value;
        }
        return $totalLate * $deduction_value * $salaryPerHour;
    }

    private function calculateOvertime($totalOvertime, $overtime_type, $overtime_value, $salaryPerHour)
    {
        if ($overtime_type == 'money') {
            return $totalOvertime * $overtime_value;
        }
        return $totalOvertime * $overtime_value * $salaryPerHour;
    }

    private function round2($value)
    {
        return bcadd($value, '0', 2);
    }
} 