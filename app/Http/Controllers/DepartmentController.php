<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    
    public function index()
    {
        $departments = Department::all();

        return response()->json($departments, 200);
    }

   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dept_name' => 'required|string|max:255',
        ]);

        $department = Department::create($validated);

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => $department
        ], 201);
    }

    public function show(Department $department)
    {
        return response()->json($department, 200);
    }

    
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'dept_name' => 'required|string|max:255',
        ]);

        $department->update($validated);

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => $department
        ], 200);
    }

    
    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully.'
        ], 200);
    }

   
   
    public function departmentsWithEmployees()
    {
        // Load departments with their employees (only selected fields)
        $departments = Department::with(['employees:id,first_name,last_name,department_id'])->get();

        return response()->json([
            'data' => $departments
        ], 200);
    }
}
