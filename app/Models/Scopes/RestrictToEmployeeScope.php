<?php

namespace App\Models\Scopes;

use App\Enums\RolesEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class RestrictToEmployeeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (
            $user->hasRole(RolesEnum::Admin->value)  
        ) {
            return;
        }

        $employeeId = $user->employee?->id;

        if (! $employeeId) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where('employee_id', $employeeId);
    }
}
