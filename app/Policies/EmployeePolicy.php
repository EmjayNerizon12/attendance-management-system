<?php

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RolesEnum::Admin->value) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('Employee.viewAny');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('Employee.view');
    }

    public function create(User $user): bool
    {
        return $user->can('Employee.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('Employee.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('Employee.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('Employee.delete');
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
        return $user->can('Employee.forceDelete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('Employee.forceDelete');
    }
}
