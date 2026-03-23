<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $this->hasPermission($user, '*') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'departments.viewAny');
    }

    public function view(User $user, Department $department): bool
    {
        return $this->hasPermission($user, 'departments.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'departments.create');
    }

    public function update(User $user, Department $department): bool
    {
        return $this->hasPermission($user, 'departments.update');
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->hasPermission($user, 'departments.delete');
    }

    public function restore(User $user, Department $department): bool
    {
        return $this->delete($user, $department);
    }

    public function forceDelete(User $user, Department $department): bool
    {
        return $this->hasPermission($user, 'departments.forceDelete');
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
