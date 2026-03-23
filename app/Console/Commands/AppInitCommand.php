<?php

namespace App\Console\Commands;

use App\Enums\EmployeeRoleEnum;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AppInitCommand extends Command
{
    protected $signature = 'app:init {--password=password : Default password for imported users}';

    protected $description = 'Initialize application data from JSON files';

    public function handle(): int
    {
        $departments = $this->importDepartmentsFromJson();
        $users = $this->importUsersFromJson();
        $employees = $this->importEmployeesFromJson();

        $this->newLine();
        $this->info("App initialization complete. Imported or updated {$departments} departments, {$users} users, and {$employees} employees.");

        return self::SUCCESS;
    }

    private function importDepartmentsFromJson(): int
    {
        $files = glob(database_path('json/departments/*.json')) ?: [];

        if ($files === []) {
            $this->warn('No JSON files found in database/json/departments.');

            return 0;
        }

        $this->info('Importing departments...');

        $departmentData = collect($files)
            ->map(fn (string $file) => $this->mapDepartmentFile($file))
            ->filter()
            ->unique('name')
            ->values();

        if ($departmentData->isEmpty()) {
            $this->warn('No valid department payloads were built from the JSON files.');

            return 0;
        }

        Department::upsert($departmentData->all(), uniqueBy: ['name'], update: ['name']);

        $departmentData->each(fn (array $department) => $this->line("Department ready: {$department['name']}"));

        return $departmentData->count();
    }

    private function importUsersFromJson(): int
    {
        $userFiles = glob(database_path('json/users/*.json')) ?: [];
        $employeeFiles = glob(database_path('json/employees/*.json')) ?: [];

        if ($userFiles === [] && $employeeFiles === []) {
            $this->warn('No JSON files found in database/json/users or database/json/employees.');

            return 0;
        }

        $this->info('Importing users...');

        $password = Hash::make((string) $this->option('password'));

        $userData = collect($userFiles)
            ->map(fn (string $file) => $this->mapUserFile($file, $password))
            ->merge(collect($employeeFiles)->map(fn (string $file) => $this->mapEmployeeFileToUser($file, $password)))
            ->filter()
            ->unique('email')
            ->values();

        if ($userData->isEmpty()) {
            $this->warn('No valid user payloads were built from the JSON files.');

            return 0;
        }

        User::upsert($userData->all(), uniqueBy: ['email'], update: ['name']);

        $userData->each(fn (array $user) => $this->line("User ready: {$user['email']} ({$user['name']})"));

        return $userData->count();
    }

    private function importEmployeesFromJson(): int
    {
        $files = glob(database_path('json/employees/*.json')) ?: [];

        if ($files === []) {
            $this->warn('No JSON files found in database/json/employees.');

            return 0;
        }

        $this->info('Importing employees...');

        $employeeData = collect($files)
            ->map(fn (string $file) => $this->mapEmployeeFile($file))
            ->filter()
            ->values();

        if ($employeeData->isEmpty()) {
            $this->warn('No valid employee payloads were built from the JSON files.');

            return 0;
        }

        $usersByEmail = User::query()
            ->whereIn('email', $employeeData->pluck('email')->all())
            ->get()
            ->keyBy('email');

        $departmentsByName = Department::query()
            ->whereIn('name', $employeeData->pluck('department')->unique()->all())
            ->get()
            ->keyBy('name');

        $employeeData = $employeeData
            ->map(function (array $employee) use ($usersByEmail, $departmentsByName) {
                $employee['user_id'] = optional($usersByEmail->get($employee['email']))->id;
                $employee['department_id'] = optional($departmentsByName->get($employee['department']))->id;
                unset($employee['department']);

                return $employee;
            })
            ->unique('email')
            ->values();

        Employee::upsert(
            $employeeData->all(),
            uniqueBy: ['email'],
            update: ['fname', 'mname', 'lname', 'suffix', 'phone', 'role', 'address', 'date_hired', 'department_id', 'user_id']
        );

        $employeeData->each(fn (array $employee) => $this->line("Employee ready: {$employee['email']}"));

        return $employeeData->count();
    }

    private function mapUserFile(string $file, string $password): ?array
    {
        $data = $this->readJsonFile($file);

        if (! $data) {
            return null;
        }

        $email = trim((string) ($data['email'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($email === '' || $name === '') {
            $this->warn("Skipping {$file}: missing email or name.");

            return null;
        }

        return [
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ];
    }

    private function mapEmployeeFileToUser(string $file, string $password): ?array
    {
        $data = $this->mapEmployeeFile($file);

        if (! $data) {
            return null;
        }

        return [
            'email' => $data['email'],
            'name' => $this->buildEmployeeName($data),
            'password' => $password,
        ];
    }

    private function mapEmployeeFile(string $file): ?array
    {
        $data = $this->readJsonFile($file);

        if (! $data) {
            return null;
        }

        $employee = [
            'fname' => trim((string) ($data['fname'] ?? '')),
            'mname' => trim((string) ($data['mname'] ?? '')) ?: null,
            'lname' => trim((string) ($data['lname'] ?? '')),
            'suffix' => trim((string) ($data['suffix'] ?? '')) ?: null,
            'email' => trim((string) ($data['email'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'role' => trim((string) ($data['role'] ?? EmployeeRoleEnum::DEFAULT->value)) ?: EmployeeRoleEnum::DEFAULT->value,
            'department' => trim((string) ($data['department'] ?? '')),
            'address' => trim((string) ($data['address'] ?? '')),
            'date_hired' => trim((string) ($data['date_hired'] ?? '')) ?: null,
        ];

        if ($employee['fname'] === '' || $employee['lname'] === '' || $employee['email'] === '' || $employee['phone'] === '' || $employee['department'] === '' || $employee['address'] === '') {
            $this->warn("Skipping {$file}: missing required employee fields.");

            return null;
        }

        if (! in_array($employee['role'], array_column(EmployeeRoleEnum::cases(), 'value'), true)) {
            $this->warn("Skipping {$file}: invalid role '{$employee['role']}'.");

            return null;
        }

        if ($employee['date_hired'] !== null && strtotime($employee['date_hired']) === false) {
            $this->warn("Skipping {$file}: invalid date_hired '{$employee['date_hired']}'.");

            return null;
        }

        return $employee;
    }

    private function mapDepartmentFile(string $file): ?array
    {
        $data = $this->readJsonFile($file);

        if (! $data) {
            return null;
        }

        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            $this->warn("Skipping {$file}: missing department name.");

            return null;
        }

        return ['name' => $name];
    }

    private function buildEmployeeName(array $employee): string
    {
        return trim(implode(' ', array_filter([
            $employee['fname'] ?? null,
            $employee['mname'] ?? null,
            $employee['lname'] ?? null,
            $employee['suffix'] ?? null,
        ])));
    }

    private function readJsonFile(string $file): ?array
    {
        $json = file_get_contents($file);
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            $this->warn("Skipping {$file}: invalid JSON. ".json_last_error_msg());

            return null;
        }

        return $data;
    }

}
