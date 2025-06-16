<?php

namespace App\Observers;

use App\Models\GeneralSetting;
use App\Http\Controllers\PayrollController;
use Illuminate\Http\Request;

class GeneralSettingObserver
{
    public function created(GeneralSetting $setting)
    {
        $this->recalculatePayroll($setting);
    }

    public function updated(GeneralSetting $setting)
    {
        $this->recalculatePayroll($setting);
    }

    private function recalculatePayroll(GeneralSetting $setting)
    {
        $month = now()->format('Y-m');
        $employee = $setting->employee;

        if ($employee) {
            $payrollController = new PayrollController();
            $request = new Request([
                'employee_id' => $employee->id,
                'month' => $month,
            ]);

            $payrollController->recalculate($request);
        }
    }
}
