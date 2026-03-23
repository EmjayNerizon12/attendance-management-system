<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $this->hasPermission($user, '*') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'employees.viewAny');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->hasPermission($user, 'employees.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'employees.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->hasPermission($user, 'employees.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->hasPermission($user, 'employees.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'employees.delete');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $this->delete($user, $employee);
    }

    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $this->hasPermission($user, 'employees.forceDelete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->hasPermission($user, 'employees.forceDelete');
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $role = $user->permissionRole();

        if (! $role) {
            return false;
        }

        $permissions = config("employee.permissions.{$role}", []);

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}
