<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HRController;




Route::post('/hr/register', [HRController::class, 'register']);
Route::post('/hr/login', [HRController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/hr/logout', [HRController::class, 'logout']);
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome HR']);
    });
});



