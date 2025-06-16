<?php

namespace App\Http\Controllers;

use App\Models\Attendence;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendenceController extends Controller
{
public function index(Request $request)
{
    $query = Attendence::with(['employee.department']);

    if ($request->filled('employee_name')) {
        $fullName = $request->employee_name;
        $query->whereHas('employee', function ($q) use ($fullName) {
            $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$fullName%"]);
        });
    }

    if ($request->filled('department_name')) {
        $query->whereHas('employee.department', function ($q) use ($request) {
            $q->where('dept_name', 'like', '%' . $request->department_name . '%');
        });
    }

    if ($request->filled('date')) {
        $query->whereDate('date', $request->date);
    }

    $attendances = $query->orderBy('date', 'desc')->get();

    $transformed = $attendances->map(function ($attendance) {
        return [
            'id' => $attendance->id,
            'date' => $attendance->date,
            'checkInTime' => $attendance->checkInTime,
            'checkOutTime' => $attendance->checkOutTime,
            'lateDurationInHours' => $attendance->lateDurationInHours,
            'overtimeDurationInHours' => $attendance->overtimeDurationInHours,
            'status' => $attendance->status,
            'employee' => [
                'id' => $attendance->employee->id,
                'full_name' => $attendance->employee->full_name,
                'profile_picture_url' => $attendance->employee->profile_image_url, 
                'email' => $attendance->employee->email,
                'dept_name' => $attendance->employee->dept_name,
            ]
        ];
    });

    return response()->json($transformed);
}


    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'employee_id' => 'required|exists:employees,id',
        'date' => 'required|date',
        'checkInTime' => 'required|date_format:H:i',
        'checkOutTime' => 'required|date_format:H:i|after:checkInTime',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $employee = Employee::with('generalSetting')->find($request->employee_id);
    $date = Carbon::parse($request->date);

    $attendanceExists = Attendence::where('employee_id', $request->employee_id)
        ->whereDate('date', $date)
        ->exists();

    if ($attendanceExists) {
        return response()->json(['error' => 'Attendance already recorded for this date'], 422);
    }

    $weekendDays = $employee->generalSetting ? $employee->generalSetting->weekend_days : [];
    if (is_string($weekendDays)) {
        $weekendDays = json_decode($weekendDays, true) ?? [];
    }

    if (in_array($date->englishDayOfWeek, $weekendDays)) {
        return response()->json(['error' => 'Cannot record attendance on a weekend'], 422);
    }


$holidayExists = \App\Models\Holiday::whereDate('date', $date)->exists();
if ($holidayExists) {
    return response()->json(['error' => 'Cannot record attendance on an official holiday'], 422);
}


    $attendance = Attendence::create([
        'employee_id' => $request->employee_id,
        'date' => $request->date,
        'checkInTime' => $request->checkInTime,
        'checkOutTime' => $request->checkOutTime,
        'lateDurationInHours' => $this->calculateLate($employee, $request->checkInTime),
        'overtimeDurationInHours' => $this->calculateOvertime($employee, $request->checkOutTime),
        'status' => 'Present',
    ]);

    if ($employee->generalSetting && $employee->generalSetting->status) {
        $attendance->status = $employee->generalSetting->status;
        $attendance->save();
    }

    return response()->json($attendance);
}

  public function update(Request $request, $id)
{
    $attendance = Attendence::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'checkInTime' => 'required|date_format:H:i',
        'checkOutTime' => 'required|date_format:H:i|after:checkInTime',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $employee = Employee::find($attendance->employee_id);

    $attendance->update([
        'checkInTime' => $request->checkInTime,
        'checkOutTime' => $request->checkOutTime,
        'lateDurationInHours' => $this->calculateLate($employee, $request->checkInTime),
        'overtimeDurationInHours' => $this->calculateOvertime($employee, $request->checkOutTime),
    ]);

    return response()->json($attendance);
}


    public function destroy($id)
    {
        $attendance = Attendence::findOrFail($id);
        $attendance->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    private function calculateLate($employee, $checkInTime)
{
    $defaultTime = Carbon::parse($employee->default_check_in_time);
    $actualTime = Carbon::parse($checkInTime);

    $lateMinutes = $defaultTime->diffInMinutes($actualTime, false);
    $lateMinutes = $lateMinutes > 0 ? $lateMinutes : 0;

    return $lateMinutes / 60;
}

private function calculateOvertime($employee, $checkOutTime)
{
    $defaultTime = Carbon::parse($employee->default_check_out_time);
    $actualTime = Carbon::parse($checkOutTime);

    $overtimeMinutes = $defaultTime->diffInMinutes($actualTime, false);
    $overtimeMinutes = $overtimeMinutes > 0 ? $overtimeMinutes : 0;

    return $overtimeMinutes / 60;
}

}
