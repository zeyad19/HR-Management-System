<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\Attendence;
use App\Models\Payroll;
use App\Models\GeneralSetting;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'address', 'salary',
        'hire_date', 'default_check_in_time', 'default_check_out_time', 
        'gender', 'nationality', 'national_id', 'birthdate', 
        'department_id', 'weekend_days', 'working_hours_per_day',
        'overtime_value', 'deduction_value', 'salary_per_hour',
        'profile_picture' 
    ];

    protected $casts = [
        'weekend_days' => 'array',
        'hire_date' => 'date',
        'birthdate' => 'date',
    ];

    protected $attributes = [
        'overtime_value' => 0,
        'deduction_value' => 0,
        'salary_per_hour' => 0,
    ];

    // Relations
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendence::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function latestPayroll()
    {
        return $this->hasOne(Payroll::class, 'employee_id')->latestOfMany();
    }

    public function generalSetting()
    {
        return $this->hasOne(GeneralSetting::class, 'employee_id');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getDeptNameAttribute()
    {
        return $this->department ? $this->department->dept_name : null;
    }

public function getProfileImageUrlAttribute()
{
    if (!$this->profile_picture) {
        return null;
    }

    return asset('employees/' . $this->profile_picture);
}


}
