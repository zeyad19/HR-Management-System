<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'month',
        'month_days',
        'attended_days',
        'absent_days',
        'total_overtime',
        'total_deduction',
        'total_deduction_amount',
        'late_deduction_amount',
        'absence_deduction_amount',
        'total_bonus_amount',
        'net_salary',
        'salary_per_hour', 
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}