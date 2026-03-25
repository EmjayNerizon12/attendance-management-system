<?php

namespace App\Console\Commands;

use App\Models\ClockIn;
use App\Models\ClockOut;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncClockInsCommand extends Command
{
    protected $signature = 'clock-ins:sync
                            {--start=2026-03-01 : Start date in Y-m-d format}
                            {--end=today : End date in Y-m-d format}
                            {--time=08:00:00 : Time to use for generated clock ins}
                            {--weekdays-only : Only generate Monday to Friday records}';

    protected $description = 'Sync missing clock ins for all employees across a date range';

    public function handle(): int
    {
        $startDate = $this->parseDateOption('start');
        $endDate = $this->parseDateOption('end');
        $time = $this->parseTimeOption();

        if (! $startDate || ! $endDate || ! $time) {
            return self::FAILURE;
        }

        if ($startDate->gt($endDate)) {
            $this->error('The start date must be on or before the end date.');

            return self::FAILURE;
        }

        $employees = Employee::query()->get();

        if ($employees->isEmpty()) {
            $this->warn('No employees found to sync clock ins for.');

            return self::SUCCESS;
        }

        $createdCount = 0;
        $clockOutCreatedCount = 0;
        $skippedCount = 0;

        $this->info(sprintf(
            'Syncing clock ins from %s to %s for %d employees.',
            $startDate->toDateString(),
            $endDate->toDateString(),
            $employees->count(),
        ));

        $progressBar = $this->output->createProgressBar($employees->count());
        $progressBar->start();

        foreach ($employees as $employee) {
            $employeeStartDate = $startDate->copy();

            if ($employee->hire_date instanceof Carbon && $employee->hire_date->gt($employeeStartDate)) {
                $employeeStartDate = $employee->hire_date->copy()->startOfDay();
            }

            if ($employeeStartDate->gt($endDate)) {
                $progressBar->advance();
                continue;
            }

            $existingDates = ClockIn::query()
                ->where('employee_id', $employee->id)
                ->whereBetween('started_at', [
                    $employeeStartDate->copy()->startOfDay(),
                    $endDate->copy()->endOfDay(),
                ])
                ->get()
                ->map(fn (ClockIn $clockIn) => $clockIn->started_at?->toDateString())
                ->filter()
                ->flip();

            $payload = [];
            $clockOutPayload = [];

            foreach (CarbonPeriod::create($employeeStartDate, $endDate) as $date) {
                if ($this->option('weekdays-only') && $date->isWeekend()) {
                    continue;
                }

                $day = $date->toDateString();

                if ($existingDates->has($day)) {
                    $skippedCount++;
                    continue;
                }

                $clockInId = (string) Str::uuid();

                $payload[] = [
                    'id' => $clockInId,
                    'employee_id' => $employee->id,
                    'started_at' => $date->copy()->setTimeFrom($time),
                    'note' => 'Synced by clock-ins:sync command',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $clockOutPayload[] = [
                    'clock_in_id' => $clockInId,
                    'ended_at' => $date->copy()->setTime(9, 0, 0),
                    'note' => 'Auto-created by clock-ins:sync command',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($payload !== []) {
                ClockIn::query()->insert($payload);
                ClockOut::query()->insert($clockOutPayload);
                $createdCount += count($payload);
                $clockOutCreatedCount += count($clockOutPayload);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Clock-in sync complete. Created {$createdCount} clock-ins, {$clockOutCreatedCount} linked clock-outs, and skipped {$skippedCount} existing clock-ins.");

        return self::SUCCESS;
    }

    private function parseDateOption(string $option): ?Carbon
    {
        $value = (string) $this->option($option);

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            $this->error("Invalid {$option} date [{$value}]. Use a valid date like 2026-03-01.");

            return null;
        }
    }

    private function parseTimeOption(): ?Carbon
    {
        $value = (string) $this->option('time');

        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable) {
            $this->error("Invalid time [{$value}]. Use H:i:s format like 08:00:00.");

            return null;
        }
    }
}
