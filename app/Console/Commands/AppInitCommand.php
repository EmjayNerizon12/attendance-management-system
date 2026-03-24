<?php

namespace App\Console\Commands;

use App\Enums\EmploymentTypeEnum;
use App\Enums\EmployeeRoleEnum;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppInitCommand extends Command
{
    protected $signature = 'app:init {--password=password : Default password for imported users}';

    protected $description = 'Initialize application data from JSON files';

    public function handle(): int
    {
        $departments = $this->importDepartmentsFromJson();
        $users = $this->importUsersFromJson();
        $employees = $this->importEmployeesFromJson();
        $authorization = $this->syncPermissionsAndRoles();
        $assignments = $this->assignRolesToUsers();

        $this->newLine();
        $this->info("App initialization complete. Imported or updated {$departments} departments, {$users} users, {$employees} employees, {$authorization['permissions']} permissions, {$authorization['roles']} roles, and {$assignments} user role assignments.");

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

        $jobTitleData = $employeeData
            ->pluck('job_title')
            ->filter()
            ->unique()
            ->map(fn (string $name): array => [
                'name' => $name,
                'description' => null,
            ])
            ->values();

        if ($jobTitleData->isNotEmpty()) {
            JobTitle::upsert($jobTitleData->all(), uniqueBy: ['name'], update: ['description']);
        }

        $departmentsByName = Department::query()
            ->whereIn('name', $employeeData->pluck('department')->unique()->all())
            ->get()
            ->keyBy('name');

        $jobTitlesByName = JobTitle::query()
            ->whereIn('name', $employeeData->pluck('job_title')->filter()->unique()->all())
            ->get()
            ->keyBy('name');

        $existingEmployeeIdsByEmail = Employee::query()
            ->whereIn('email', $employeeData->pluck('email')->all())
            ->pluck('id', 'email');

        $employeeData = $employeeData
            ->map(function (array $employee) use ($usersByEmail, $departmentsByName, $jobTitlesByName, $existingEmployeeIdsByEmail) {
                $employee['id'] = $existingEmployeeIdsByEmail->get($employee['email']) ?? (string) Str::uuid();
                $employee['user_id'] = optional($usersByEmail->get($employee['email']))->id;
                $employee['department_id'] = optional($departmentsByName->get($employee['department']))->id;
                $employee['job_title_id'] = optional($jobTitlesByName->get($employee['job_title']))->id;
                unset($employee['department']);
                unset($employee['job_title']);

                return $employee;
            })
            ->unique('email')
            ->values();

        Employee::upsert(
            $employeeData->all(),
            uniqueBy: ['email'],
            update: ['fname', 'mname', 'lname', 'suffix', 'phone', 'role', 'employment_type', 'address', 'hire_date', 'salary', 'department_id', 'job_title_id', 'user_id']
        );

        $employeeData->each(fn (array $employee) => $this->line("Employee ready: {$employee['email']}"));

        return $employeeData->count();
    }

    private function syncPermissionsAndRoles(): array
    {
        $this->info('Syncing roles and permissions...');

        $guard = config('auth.defaults.guard', 'web');
        $permissionsByRole = collect(config('employee.permissions', []));

        $permissionNames = $permissionsByRole
            ->flatten()
            ->unique()
            ->values();

        $permissionNames->each(function (string $permission) use ($guard): void {
            Permission::findOrCreate($permission, $guard);
        });

        $permissionsByRole->each(function (array $permissions, string $roleName) use ($guard): void {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($permissions);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'permissions' => $permissionNames->count(),
            'roles' => $permissionsByRole->count(),
        ];
    }


    private function assignRolesToUsers(): int
    {
        $this->info('Assigning roles to users...');

        return Employee::query()
            ->whereNotNull('user_id')
            ->get()
            ->reduce(function (int $count, Employee $employee): int {
                $roleName = $employee->role instanceof \BackedEnum
                    ? $employee->role->value
                    : (string) $employee->role;

                if ($roleName === '') {
                    return $count;
                }

                $user = User::query()->find($employee->user_id);

                if (! $user) {
                    $this->warn("User not found for employee email '{$employee->email}'.");

                    return $count;
                }

                $this->assignRoleToUser($user, $roleName);

                return $count + 1;
            }, 0);
    }

    private function assignRoleToUser(User $user, string $roleName): void
    {
        $user->syncRoles([$roleName]);
        $this->line("Assigned role '{$roleName}' to user '{$user->email}'.");
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
            'employment_type' => trim((string) ($data['employment_type'] ?? EmploymentTypeEnum::FullTime->value)) ?: EmploymentTypeEnum::FullTime->value,
            'department' => trim((string) ($data['department'] ?? '')),
            'job_title' => trim((string) ($data['job_title'] ?? '')) ?: null,
            'address' => trim((string) ($data['address'] ?? '')),
            'hire_date' => trim((string) ($data['hire_date'] ?? ($data['date_hired'] ?? ''))) ?: null,
            'salary' => filled($data['salary'] ?? null) ? (float) $data['salary'] : null,
        ];

        if ($employee['fname'] === '' || $employee['lname'] === '' || $employee['email'] === '' || $employee['phone'] === '' || $employee['department'] === '' || $employee['address'] === '') {
            $this->warn("Skipping {$file}: missing required employee fields.");

            return null;
        }

        if (! in_array($employee['role'], array_column(EmployeeRoleEnum::cases(), 'value'), true)) {
            $this->warn("Skipping {$file}: invalid role '{$employee['role']}'.");

            return null;
        }

        if (! in_array($employee['employment_type'], array_column(EmploymentTypeEnum::cases(), 'value'), true)) {
            $this->warn("Skipping {$file}: invalid employment_type '{$employee['employment_type']}'.");

            return null;
        }

        if ($employee['hire_date'] !== null && strtotime($employee['hire_date']) === false) {
            $this->warn("Skipping {$file}: invalid hire_date '{$employee['hire_date']}'.");
 
            return null;
        }

        if (($employee['salary'] !== null) && (! is_numeric($employee['salary']))) {
            $this->warn("Skipping {$file}: invalid salary '{$employee['salary']}'.");

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
