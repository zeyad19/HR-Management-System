<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'salary',
        'hire_date',
        'default_check_in_time',
        'default_check_out_time',
        'gender',
        'nationality',
        'national_id',
        'birthdate',
        'department_id',
        'weekend_days',
        'working_hours_per_day',
        'overtime_value',
        'deduction_value',
        'salary_per_hour',
    ];

    protected $casts = [
        'weekend_days' => 'array',
        'hire_date' => 'date',
        'birthdate' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
