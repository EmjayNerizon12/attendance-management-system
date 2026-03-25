<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function clockIns()
    {
        return $this->hasMany(ClockIn::class);
    }
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim(implode(' ', array_filter([
                $this->fname,
                $this->mname,
                $this->lname,
                $this->suffix,
            ]))),
        );
    }

    protected $casts = [
        'role' => \App\Enums\EmployeeRoleEnum::class,
        'employment_type' => \App\Enums\EmploymentTypeEnum::class,
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];
}
