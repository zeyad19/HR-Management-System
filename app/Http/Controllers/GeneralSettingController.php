<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Models\Employee;

class GeneralSettingController extends Controller
{
    // Retrieve the first general setting record
    public function index()
    {
        $settings = GeneralSetting::first();

        return response()->json([
            'success' => true,
            'message' => $settings ? 'General settings retrieved successfully.' : 'No general settings found.',
            'data' => $settings
        ]);
    }

    // Create or update general settings by employee_id
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'deduction_type' => 'required|in:hours,money',
            'deduction_value' => 'required|numeric',
            'overtime_type' => 'required|in:hours,money',
            'overtime_value' => 'required|numeric',
            'weekend_days' => 'required|array',
            'weekend_days.*' => 'string',
        ]);
        $employee = Employee::find($validated['employee_id']);
if ($employee) {
 
    $employee->weekend_days = json_encode($validated['weekend_days']);
    

    $employee->save();
}



        $setting = GeneralSetting::where('employee_id', $validated['employee_id'])->first();

        if ($setting) {
            $setting->update($validated);
            $message = 'General setting updated successfully.';
        } else {
            $setting = GeneralSetting::create($validated);
            $message = 'General setting created successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $setting
        ], 201);
    }

    // Show general setting by ID
    public function show($id)
    {
        $setting = GeneralSetting::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'General setting retrieved successfully.',
            'data' => $setting
        ]);
    }

    // Update general setting by ID (supports partial update)
  public function update(Request $request, $id)
{
    $validated = $request->validate([
        'employee_id' => 'sometimes|exists:employees,id',
        'deduction_type' => 'sometimes|in:hours,money',
        'deduction_value' => 'sometimes|numeric',
        'overtime_type' => 'sometimes|in:hours,money',
        'overtime_value' => 'sometimes|numeric',
        'weekend_days' => 'sometimes|array',
        'weekend_days.*' => 'string',
    ]);

    // تحقق من وجود employee_id قبل استخدامه
    if (isset($validated['employee_id'])) {
        $employee = Employee::find($validated['employee_id']);
        if ($employee) {
            if (isset($validated['weekend_days'])) {
                $employee->weekend_days = json_encode($validated['weekend_days']);
            }
           
            $employee->save();
        }
    }

    $setting = GeneralSetting::findOrFail($id);

    $setting->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'General setting updated successfully.',
        'data' => $setting
    ]);
}

    // Delete general setting by ID
    public function destroy($id)
    {
        $setting = GeneralSetting::findOrFail($id);
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'General setting deleted successfully.'
        ]);
    }
}

