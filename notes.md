# Spatie Permission Notes

## Overview

This project uses `spatie/laravel-permission` for:

- roles
- permissions
- assigning roles to users
- checking access with `$user->hasRole(...)`
- checking access with `$user->can(...)`

The permission names used in this project follow this format:

- `Employee.viewAny`
- `Employee.create`
- `Department.update`

## How It Works

### 1. Spatie package config

[`config/permission.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/config/permission.php)

This is the Spatie package configuration.

It controls:

- permission model
- role model
- table names
- pivot column names
- cache settings
- whether permission checks are registered on Laravel Gate

Important current values:

- `register_permission_check_method => true`
- `teams => false`
- default table names:
  - `roles`
  - `permissions`
  - `model_has_roles`
  - `model_has_permissions`
  - `role_has_permissions`

### 2. App permission source

[`config/employee.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/config/employee.php)

This file is the app-level source of truth for which role gets which permissions.

Example:

- `admin` gets full employee and department permissions
- `manager` gets limited create/view/update permissions
- `supervisor` gets view permissions
- `staff` gets basic employee view permission

### 3. Role enum

[`app/Enums/RolesEnum.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Enums/RolesEnum.php)

This enum defines the role names used by the project:

- `admin`
- `manager`
- `supervisor`
- `staff`

### 4. User model

[`app/Models/User.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Models/User.php)

The `User` model uses:

```php
use Spatie\Permission\Traits\HasRoles;
```

This is required so that users can use:

```php
$user->hasRole('admin');
$user->can('Employee.create');
```

### 5. Policy checks

Policies use Spatie permission checks directly.

Files:

- [`app/Policies/EmployeePolicy.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Policies/EmployeePolicy.php)
- [`app/Policies/DepartmentPolicy.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Policies/DepartmentPolicy.php)

Examples:

```php
$user->can('Employee.create');
$user->can('Department.update');
```

Admin bypass is done with:

```php
$user->hasRole(RolesEnum::Admin->value)
```

## Database Tables

The Spatie tables are created by:

[`database/migrations/2026_03_23_080000_create_permission_tables.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/database/migrations/2026_03_23_080000_create_permission_tables.php)

This migration creates:

- `permissions`
- `roles`
- `model_has_permissions`
- `model_has_roles`
- `role_has_permissions`

## Sync Flow

The sync is done inside:

[`app/Console/Commands/AppInitCommand.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Console/Commands/AppInitCommand.php)

### Current flow in `app:init`

1. Import departments from JSON
2. Import users from JSON
3. Import employees from JSON
4. Sync permissions and roles from `config/employee.php`
5. Assign each employee role to the linked user

### Permission and role sync

Method:

- `syncPermissionsAndRoles()`

What it does:

- reads `config('employee.permissions')`
- creates permissions with `Permission::findOrCreate(...)`
- creates roles with `Role::findOrCreate(...)`
- attaches permissions to roles with `$role->syncPermissions(...)`
- clears Spatie permission cache

### User role assignment

Methods:

- `assignRolesToUsers()`
- `assignRoleToUser()`

What it does:

- reads each imported employee with `user_id`
- gets the employee role
- assigns that role to the linked user with:

```php
$user->syncRoles([$roleName]);
```

`syncRoles()` is used so re-running `app:init` keeps the user role in sync instead of stacking old roles.

## Command To Use

Run this command after migrations:

```bash
php artisan app:init
```

This command now handles:

- importing JSON data
- creating permissions
- creating roles
- attaching permissions to roles
- assigning roles to users

## Files Updated

The following existing files were updated for this setup:

- [`app/Console/Commands/AppInitCommand.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Console/Commands/AppInitCommand.php)
- [`app/Models/User.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Models/User.php)
- [`app/Policies/EmployeePolicy.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Policies/EmployeePolicy.php)
- [`app/Policies/DepartmentPolicy.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Policies/DepartmentPolicy.php)
- [`config/employee.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/config/employee.php)

## Files Created

The following files were created for this setup:

- [`app/Enums/RolesEnum.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/app/Enums/RolesEnum.php)
- [`config/permission.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/config/permission.php)
- [`database/migrations/2026_03_23_080000_create_permission_tables.php`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/database/migrations/2026_03_23_080000_create_permission_tables.php)
- [`notes.md`](/c:/Users/Em-jay/Desktop/Project/attendance-management-system/notes.md)

## Files Removed

This setup no longer uses a separate permission seeder.

Removed:

- `database/seeders/PermissionSeeder.php`

## Example Usage

### Check role

```php
auth()->user()->hasRole('admin');
```

### Check permission

```php
auth()->user()->can('Employee.create');
auth()->user()->can('Department.viewAny');
```

### Inside policy

```php
public function create(User $user): bool
{
    return $user->can('Employee.create');
}
```

## Notes

- `config/permission.php` is Spatie's package config
- `config/employee.php` is this app's role-permission map
- the database is the real source used by Spatie at runtime
- `app:init` is responsible for syncing config into the database
