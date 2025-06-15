<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_can_be_created()
    {
        $payroll = Payroll::factory()->create();

        $this->assertDatabaseHas('payrolls', [
            'id' => $payroll->id,
            'employee_id' => $payroll->employee_id,
        ]);
    }
}
