<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    EmployeeController,
    HRController,
    AttendenceController,
    PayrollController,
    GeneralSettingController,
    HolidayController,
    DepartmentController
};

// Public routes
Route::post('/hr/register', [HRController::class, 'register']);
Route::post('/hr/login', [HRController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // HR routes
    Route::post('/hr/logout', [HRController::class, 'logout']);
    Route::get('/dashboard', fn() => response()->json(['message' => 'Welcome HR']));

    // Employees routes
    Route::apiResource('employees', EmployeeController::class);
    Route::get('/employees/search', [EmployeeController::class, 'search']);


    // Attendances routes
    Route::apiResource('attendances', AttendenceController::class);

    // Payroll routes
   Route::prefix('payroll')->group(function () {
    Route::get('/show', [PayrollController::class, 'show']);
    Route::get('/summary', [PayrollController::class, 'summary']);
  Route::get('/all-employees-data', [PayrollController::class, 'allEmployeesData']);
    
    Route::post('/recalculate', [PayrollController::class, 'recalculate']);
    Route::get('/all', [PayrollController::class, 'allPayrolls']); 
});

Route::get('/payroll/verify/{employee_id}/{month}', [PayrollController::class, 'verifyPayrollApi']);

    // Settings routes
    Route::apiResource('settings',GeneralSettingController::class);
   
    // Holidays routes 
    Route::apiResource('holidays', HolidayController::class);
    

    // Departments routes
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/departments-with-employees', [DepartmentController::class, 'departmentsWithEmployees']);

});