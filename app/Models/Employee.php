<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model 
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }
    protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => trim(implode(' ', array_filter([
            $this->fname,
            $this->mname,
            $this->lname,
            $this->suffix,
        ]))),
    );
}

    protected $casts = [
        'role' => \App\Enums\EmployeeRoleEnum::class,
        'date_hired' => 'date',
        'deleted_at' => 'datetime',
    ];
}
