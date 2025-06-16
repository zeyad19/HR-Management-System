<?php

namespace App\Observers;

use App\Models\Employee;
use App\Http\Controllers\PayrollController;
use Illuminate\Http\Request;

class EmployeeObserver
{
    public function created(Employee $employee)
    {
        $this->recalculatePayroll($employee);
    }

    public function updated(Employee $employee)
    {
        $this->recalculatePayroll($employee);
    }

    private function recalculatePayroll(Employee $employee)
    {
        $month = now()->format('Y-m');
        $payrollController = new PayrollController();
        $request = new Request([
            'employee_id' => $employee->id,
            'month' => $month,
        ]);

        $payrollController->recalculate($request);
    }
}
