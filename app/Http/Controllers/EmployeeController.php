<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('department')->paginate(10);
        return response()->json($employees);
    }

    public function create()
    {
        $departments = Department::all();
        return response()->json(['departments' => $departments]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|digits_between:11,15|numeric',
            'address' => 'nullable|string',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date|after_or_equal:2008-01-01',
            'default_check_in_time' => 'nullable|date_format:H:i',
            'default_check_out_time' => 'nullable|date_format:H:i',
            'gender' => 'required|in:Male,Female',
            'nationality' => 'required|string',
            'national_id' => 'required|digits:14|unique:employees,national_id',
            'birthdate' => 'required|date|before_or_equal:' . Carbon::now()->subYears(20)->toDateString(),
            'department_id' => 'required|exists:departments,id',
            'weekend_days' => 'nullable|array',
            'weekend_days.*' => 'in:Friday,Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday',
            'working_hours_per_day' => 'required|integer|min:1|max:24',
            'overtime_value' => 'nullable|numeric|min:0',
            'deduction_value' => 'nullable|numeric|min:0',
            'salary_per_hour' => 'nullable|numeric|min:0',
        ], $this->validationMessages());

        $validatedData['weekend_days'] = $validatedData['weekend_days'] ?? [];

        $employee = Employee::create($validatedData);

        return response()->json([
            'message' => 'Employee added successfully!',
            'employee' => $employee
        ], 201);
    }

    public function show($id)
    {
        $employee = Employee::with('department')->findOrFail($id);
        return response()->json($employee);
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();

        return response()->json([
            'employee' => $employee,
            'departments' => $departments
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'required|digits_between:11,15|numeric',
            'address' => 'nullable|string',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date|after_or_equal:2008-01-01',
            'default_check_in_time' => 'nullable|date_format:H:i',
            'default_check_out_time' => 'nullable|date_format:H:i',
            'gender' => 'required|in:Male,Female',
            'nationality' => 'required|string',
            'national_id' => 'required|digits:14|unique:employees,national_id,' . $id,
            'birthdate' => 'required|date|before_or_equal:' . Carbon::now()->subYears(20)->toDateString(),
            'department_id' => 'required|exists:departments,id',
            'weekend_days' => 'nullable|array',
            'weekend_days.*' => 'in:Friday,Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday',
            'working_hours_per_day' => 'required|integer|min:1|max:24',
            'overtime_value' => 'nullable|numeric|min:0',
            'deduction_value' => 'nullable|numeric|min:0',
            'salary_per_hour' => 'nullable|numeric|min:0',
        ], $this->validationMessages());

        $validatedData['weekend_days'] = $validatedData['weekend_days'] ?? [];

        $employee->update($validatedData);

        return response()->json([
            'message' => 'Employee updated successfully!',
            'employee' => $employee
        ]);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully!']);
    }

    private function validationMessages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be valid.',
            'email.unique' => 'Email is already taken.',
            'phone.required' => 'Phone number is required.',
            'phone.digits_between' => 'Phone must be between 11 and 15 digits.',
            'phone.numeric' => 'Phone must contain digits only.',
            'salary.required' => 'Salary is required.',
            'salary.numeric' => 'Salary must be a number.',
            'hire_date.required' => 'Hire date is required.',
            'hire_date.after_or_equal' => 'Hire date must be after or equal to 2008-01-01.',
            'national_id.required' => 'National ID is required.',
            'national_id.digits' => 'National ID must be exactly 14 digits.',
            'national_id.unique' => 'National ID is already in use.',
            'birthdate.required' => 'Birthdate is required.',
            'birthdate.before_or_equal' => 'Employee must be at least 20 years old.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Department must be valid.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be Male or Female.',
            'working_hours_per_day.required' => 'Working hours is required.',
            'working_hours_per_day.integer' => 'Working hours must be an integer.',
            'working_hours_per_day.min' => 'Working hours must be at least 1.',
            'working_hours_per_day.max' => 'Working hours must not exceed 24.',
        ];
    }
}
