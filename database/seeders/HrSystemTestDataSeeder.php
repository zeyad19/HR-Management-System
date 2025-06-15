<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HR;
use App\Models\Department;
use App\Models\Employee;
use App\Models\GeneralSetting;
use App\Models\Holiday;
use App\Models\Attendence;
use App\Models\Payroll;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HrSystemTestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create HR (check if exists)
        HR::firstOrCreate(
            ['email' => 'admin@hrsystem.com'],
            [
                'name' => 'Admin HR',
                'password' => Hash::make('password123'),
                'profile_picture' => null, // Avoid file path issue
            ]
        );

        // 2. Create Departments
        $dept1 = Department::firstOrCreate(['dept_name' => 'Engineering']);
        $dept2 = Department::firstOrCreate(['dept_name' => 'Human Resources']);

        // 3. Create Employees
        $employee1 = Employee::firstOrCreate(
            ['national_id' => '12345678901234'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@hrsystem.com',
                'phone' => '123456789012',
                'address' => '123 Main St, Cairo',
                'salary' => 6000.00,
                'hire_date' => '2023-01-01',
                'default_check_in_time' => '09:00',
                'default_check_out_time' => '17:00',
                'gender' => 'Female',
                'nationality' => 'Egyptian',
                'birthdate' => '1995-05-15',
                'department_id' => $dept1->id,
                'weekend_days' => ['Friday', 'Saturday'],
                'working_hours_per_day' => 8,
                'profile_picture' => null,
            ]
        );

        $employee2 = Employee::firstOrCreate(
            ['national_id' => '98765432101234'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@hrsystem.com',
                'phone' => '987654321012',
                'address' => '456 Elm St, Giza',
                'salary' => 5000.00,
                'hire_date' => '2023-06-01',
                'default_check_in_time' => '08:30',
                'default_check_out_time' => '16:30',
                'gender' => 'Male',
                'nationality' => 'Egyptian',
                'birthdate' => '1990-10-10',
                'department_id' => $dept2->id,
                'weekend_days' => ['Friday', 'Saturday'],
                'working_hours_per_day' => 8,
                'profile_picture' => null,
            ]
        );

        // 4. Create General Settings
        GeneralSetting::firstOrCreate(
            ['employee_id' => $employee1->id],
            [
                'deduction_type' => 'hours',
                'deduction_value' => 10.00,
                'overtime_type' => 'money',
                'overtime_value' => 15.00,
                'weekend_days' => ['Friday', 'Saturday'],
            ]
        );

        GeneralSetting::firstOrCreate(
            ['employee_id' => $employee2->id],
            [
                'deduction_type' => 'money',
                'deduction_value' => 20.00,
                'overtime_type' => 'hours',
                'overtime_value' => 1.5,
                'weekend_days' => ['Friday', 'Saturday'],
            ]
        );

        // 5. Create Holidays
        Holiday::firstOrCreate(
            ['date' => '2025-12-25'],
            ['name' => 'Christmas']
        );

        Holiday::firstOrCreate(
            ['date' => '2025-01-01'],
            ['name' => 'New Year']
        );

        // 6. Create Attendances
        Attendence::firstOrCreate(
            [
                'employee_id' => $employee1->id,
                'date' => '2025-06-01',
            ],
            [
                'checkInTime' => '09:15',
                'checkOutTime' => '17:30',
                'lateDurationInHours' => 0.25,
                'overtimeDurationInHours' => 0.5,
                'status' => 'Present',
            ]
        );

        Attendence::firstOrCreate(
            [
                'employee_id' => $employee1->id,
                'date' => '2025-06-02',
            ],
            [
                'checkInTime' => '09:00',
                'checkOutTime' => '17:00',
                'lateDurationInHours' => 0,
                'overtimeDurationInHours' => 0,
                'status' => 'Present',
            ]
        );

        Attendence::firstOrCreate(
            [
                'employee_id' => $employee2->id,
                'date' => '2025-06-01',
            ],
            [
                'checkInTime' => null,
                'checkOutTime' => null,
                'lateDurationInHours' => 0,
                'overtimeDurationInHours' => 0,
                'status' => 'Absent',
            ]
        );

        // 7. Create Payroll
        Payroll::firstOrCreate(
            [
                'employee_id' => $employee1->id,
                'month' => '2025-06',
            ],
            [
                'month_days' => 30,
                'attended_days' => 20,
                'absent_days' => 2,
                'total_overtime' => 5.0,
                'total_deduction' => 16.0,
                'total_deduction_amount' => 400.00,
                'late_deduction_amount' => 50.00,
                'absence_deduction_amount' => 350.00,
                'total_bonus_amount' => 75.00,
                'net_salary' => 5675.00,
                'salary_per_hour' => 25.00, // Now valid since migration added this column
            ]
        );

        Payroll::firstOrCreate(
            [
                'employee_id' => $employee2->id,
                'month' => '2025-06',
            ],
            [
                'month_days' => 30,
                'attended_days' => 18,
                'absent_days' => 4,
                'total_overtime' => 2.0,
                'total_deduction' => 32.0,
                'total_deduction_amount' => 800.00,
                'late_deduction_amount' => 100.00,
                'absence_deduction_amount' => 700.00,
                'total_bonus_amount' => 37.50,
                'net_salary' => 4237.50,
                'salary_per_hour' => 20.83,
            ]
        );
    }
}