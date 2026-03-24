<?php

use App\Enums\EmploymentTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'date_hired') && (! Schema::hasColumn('employees', 'hire_date'))) {
                $table->renameColumn('date_hired', 'hire_date');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'job_title_id')) {
                $table->foreignId('job_title_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('job_titles')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('employees', 'employment_type')) {
                $table->string('employment_type')
                    ->default(EmploymentTypeEnum::FullTime->value)
                    ->after('role');
            }

            if (! Schema::hasColumn('employees', 'salary')) {
                $table->decimal('salary', 12, 2)
                    ->nullable()
                    ->after('hire_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'job_title_id')) {
                $table->dropConstrainedForeignId('job_title_id');
            }

            if (Schema::hasColumn('employees', 'employment_type')) {
                $table->dropColumn('employment_type');
            }

            if (Schema::hasColumn('employees', 'salary')) {
                $table->dropColumn('salary');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'hire_date') && (! Schema::hasColumn('employees', 'date_hired'))) {
                $table->renameColumn('hire_date', 'date_hired');
            }
        });
    }
};
