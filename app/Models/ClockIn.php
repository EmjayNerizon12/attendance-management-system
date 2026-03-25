<?php

namespace App\Models;

use App\Models\Scopes\RestrictToEmployeeScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClockIn extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'started_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new RestrictToEmployeeScope());
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    protected function startedDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->started_at
                ? $this->started_at
                    ->copy()
                    ->setTimezone(config('employee.timezone', config('app.timezone')))
                    ->format('F d, Y')
                : null,
        );
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
