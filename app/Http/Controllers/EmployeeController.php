<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Get a paginated list of employees along with their department data.
     */
    public function index()
    {
        $employees = Employee::with('department')->paginate(10);
        return response()->json($employees, 200);
    }

    /**
     * Fetch all departments to be used when creating a new employee.
     */
    public function create()
    {
        $departments = Department::all();
        return response()->json(['departments' => $departments], 200);
    }

    /**
     * Store a new employee record in the database.
     * Handle validation, image upload, and save all employee data.
     */
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
            'department_id' => 'nullable|exists:departments,id',
            'weekend_days' => 'nullable|array',
            'weekend_days.*' => 'in:Friday,Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday',
            'working_hours_per_day' => 'required|integer|min:1|max:24',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
            $path = $request->file('profile_picture')->store('employees', 'public');
            $validatedData['profile_picture'] = $path;
        }

        // Default empty array if weekend_days not provided
        $validatedData['weekend_days'] = $validatedData['weekend_days'] ?? [];

        $employee = Employee::create($validatedData);

        return response()->json([
            'message' => 'Employee added successfully.',
            'employee' => $employee
        ], 201);
    }

    /**
     * Display a single employee data with department details.
     */
    public function show($id)
    {
        $employee = Employee::with('department')->findOrFail($id);
        return response()->json($employee, 200);
    }

    /**
     * Get data for editing a specific employee including department list.
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $departments = Department::all();

        return response()->json([
            'employee' => $employee,
            'departments' => $departments
        ], 200);
    }

    /**
     * Update employee data partially or fully.
     * Handle profile picture replacement if uploaded.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validatedData = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:employees,email,' . $id,
            'phone' => 'sometimes|required|digits_between:11,15|numeric',
            'address' => 'nullable|string',
            'salary' => 'sometimes|required|numeric|min:0',
            'hire_date' => 'sometimes|required|date|after_or_equal:2008-01-01',
            'default_check_in_time' => 'nullable|date_format:H:i',
            'default_check_out_time' => 'nullable|date_format:H:i',
            'gender' => 'sometimes|required|in:Male,Female',
            'nationality' => 'sometimes|required|string',
            'national_id' => 'sometimes|required|digits:14|unique:employees,national_id,' . $id,
            'birthdate' => 'sometimes|required|date|before_or_equal:' . Carbon::now()->subYears(20)->toDateString(),
            'department_id' => 'nullable|exists:departments,id',
            'weekend_days' => 'nullable|array',
            'weekend_days.*' => 'in:Friday,Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday',
            'working_hours_per_day' => 'sometimes|required|integer|min:1|max:24',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle new profile picture upload and delete old image if exists
        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
            if ($employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture)) {
                Storage::disk('public')->delete($employee->profile_picture);
            }
            $path = $request->file('profile_picture')->store('employees', 'public');
            $validatedData['profile_picture'] = $path;
        }

        // Preserve old weekend_days if not updated
        if (!array_key_exists('weekend_days', $validatedData)) {
            $validatedData['weekend_days'] = $employee->weekend_days;
        }

        $employee->update($validatedData);

        return response()->json([
            'message' => 'Employee updated successfully.',
            'employee' => $employee
        ], 200);
    }

    /**
     * Delete an employee record and their profile picture from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->profile_picture && Storage::disk('public')->exists($employee->profile_picture)) {
            Storage::disk('public')->delete($employee->profile_picture);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully.'], 200);
    }

    /**
     * Search employees by full name or national ID with pagination.
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->input('query');

        $employees = Employee::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"])
            ->orWhere('national_id', 'like', "%{$query}%")
            ->with('department')
            ->paginate(10);

        return response()->json($employees, 200);
    }
}
