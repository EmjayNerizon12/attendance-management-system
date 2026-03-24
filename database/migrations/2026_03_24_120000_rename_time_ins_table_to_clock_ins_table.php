<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('time_ins') && (! Schema::hasTable('clock_ins'))) {
            Schema::rename('time_ins', 'clock_ins');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clock_ins') && (! Schema::hasTable('time_ins'))) {
            Schema::rename('clock_ins', 'time_ins');
        }
    }
};
