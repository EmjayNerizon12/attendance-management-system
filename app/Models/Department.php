<?php

namespace App\Models;

use App\Enums\EmployeeRoleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function head()
    {
        return $this->hasOne(Employee::class)->where('role', EmployeeRoleEnum::Manager);
    }

    public function supervisor()
    {
        return $this->hasOne(Employee::class)->where('role', EmployeeRoleEnum::Supervisor);
    }
}
