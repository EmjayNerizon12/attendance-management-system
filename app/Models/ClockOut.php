<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClockOut extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function clockIn()
    {
        return $this->belongsTo(ClockIn::class);
    }
}
