<?php

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RolesEnum::Admin->value) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('Department.viewAny');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->can('Department.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Department.create');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('Department.update');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->can('Department.delete');
    }

    public function restore(User $user, Department $department): bool
    {
        return $this->delete($user, $department);
    }

    public function forceDelete(User $user, Department $department): bool
    {
        return $user->can('Department.forceDelete');
    }
}
