<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Employee;
use App\Models\Attendence;
use App\Http\Controllers\PayrollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GeneralSettingController extends Controller
{
    /**
     * Retrieve the first general setting record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = GeneralSetting::first();

        return response()->json([
            'success' => true,
            'message' => $settings ? 'General settings retrieved successfully.' : 'No general settings found.',
            'data' => $settings
        ]);
    }

    /**
     * Create or update general settings by employee_id and recalculate payroll.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'deduction_type' => 'required|in:hours,money',
            'deduction_value' => 'required|numeric',
            'overtime_type' => 'required|in:hours,money',
            'overtime_value' => 'required|numeric',
            'weekend_days' => 'required|array',
            'weekend_days.*' => 'string',
        ]);

        // Update employee record
        $employee = Employee::findOrFail($validated['employee_id']);
        $employee->weekend_days = json_encode($validated['weekend_days']);
        $employee->deduction_value = $validated['deduction_value'];
        $employee->overtime_value = $validated['overtime_value'];
        $employee->save();

        // Create or update general setting
        $setting = GeneralSetting::where('employee_id', $validated['employee_id'])->first();
        $message = 'General setting created successfully.';
        if ($setting) {
            $setting->update($validated);
            $message = 'General setting updated successfully.';
        } else {
            $setting = GeneralSetting::create($validated);
        }

        // Recalculate payroll for all months with attendance records
        try {
            $payrollController = new PayrollController();
            $attendanceMonths = Attendence::where('employee_id', $validated['employee_id'])
                ->selectRaw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month')
                ->pluck('month');

            foreach ($attendanceMonths as $month) {
                $recalculateRequest = new Request([
                    'employee_id' => $validated['employee_id'],
                    'month' => $month,
                ]);
                $payrollResponse = $payrollController->recalculate($recalculateRequest);

                if (!$payrollResponse->getData()->success) {
                    Log::warning("Failed to recalculate payroll for employee_id: {$validated['employee_id']}, month: {$month}. Error: " . $payrollResponse->getData()->message);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error recalculating payroll for employee_id: {$validated['employee_id']}. Exception: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $setting
        ], 201);
    }

    /**
     * Show general setting by employee_id.
     *
     * @param int $employee_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($employee_id)
    {
        $setting = GeneralSetting::where('employee_id', $employee_id)->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'General setting retrieved successfully.',
            'data' => $setting
        ]);
    }

    /**
     * Update general setting by employee_id (supports partial update) and recalculate payroll.
     *
     * @param Request $request
     * @param int $employee_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $employee_id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'deduction_type' => 'sometimes|in:hours,money',
            'deduction_value' => 'sometimes|numeric',
            'overtime_type' => 'sometimes|in:hours,money',
            'overtime_value' => 'sometimes|numeric',
            'weekend_days' => 'sometimes|array',
            'weekend_days.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Check if employee exists
        $employee = Employee::findOrFail($employee_id);

        // Update employee record if relevant fields are provided
        if (isset($validated['weekend_days'])) {
            $employee->weekend_days = json_encode($validated['weekend_days']);
        }
        if (isset($validated['deduction_value'])) {
            $employee->deduction_value = $validated['deduction_value'];
        }
        if (isset($validated['overtime_value'])) {
            $employee->overtime_value = $validated['overtime_value'];
        }
        $employee->save();

        // Update general setting
        $setting = GeneralSetting::where('employee_id', $employee_id)->firstOrFail();
        $setting->update($validated);

        // Recalculate payroll for all months with attendance records
        try {
            $payrollController = new PayrollController();
            $attendanceMonths = Attendence::where('employee_id', $employee_id)
                ->selectRaw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month')
                ->pluck('month');

            foreach ($attendanceMonths as $month) {
                $recalculateRequest = new Request([
                    'employee_id' => $employee_id,
                    'month' => $month,
                ]);
                $payrollResponse = $payrollController->recalculate($recalculateRequest);

                if (!$payrollResponse->getData()->success) {
                    Log::warning("Failed to recalculate payroll for employee_id: {$employee_id}, month: {$month}. Error: " . $payrollResponse->getData()->message);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error recalculating payroll for employee_id: {$employee_id}. Exception: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'General setting updated successfully.',
            'data' => $setting
        ]);
    }

    /**
     * Delete general setting by employee_id and recalculate payroll.
     *
     * @param int $employee_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($employee_id)
    {
        // Check if employee exists
        Employee::findOrFail($employee_id);

        // Find and delete general setting
        $setting = GeneralSetting::where('employee_id', $employee_id)->first();
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'No general setting found for this employee.'
            ], 404);
        }

        // Store employee data before deleting setting
        $attendanceMonths = Attendence::where('employee_id', $employee_id)
            ->selectRaw('DISTINCT DATE_FORMAT(date, "%Y-%m") as month')
            ->pluck('month');

        // Delete the general setting
        $setting->delete();

        // Reset employee fields
        $employee = Employee::find($employee_id);
        $employee->weekend_days = json_encode(['Friday', 'Saturday']); // Default value
        $employee->deduction_value = 0.00;
        $employee->overtime_value = 0.00;
        $employee->save();

        // Recalculate payroll for all months with attendance records
        try {
            $payrollController = new PayrollController();
            foreach ($attendanceMonths as $month) {
                $recalculateRequest = new Request([
                    'employee_id' => $employee_id,
                    'month' => $month,
                ]);
                $payrollResponse = $payrollController->recalculate($recalculateRequest);

                if (!$payrollResponse->getData()->success) {
                    Log::warning("Failed to recalculate payroll for employee_id: {$employee_id}, month: {$month}. Error: " . $payrollResponse->getData()->message);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error recalculating payroll for employee_id: {$employee_id}. Exception: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'General setting deleted successfully.'
        ]);
    }
}