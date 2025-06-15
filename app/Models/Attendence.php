<?php

// App/Models/Attendance.php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendence extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'attendances';
    

    protected $fillable = [
        'employee_id', 'date', 'checkInTime', 'checkOutTime',
        'lateDurationInHours', 'overtimeDurationInHours', 'status'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
