<?php

use App\Enums\RolesEnum;

return [
    'permissions' => [
        RolesEnum::Admin->value => [
            'Department.viewAny',
            'Department.view',
            'Department.create',
            'Department.update',
            'Department.delete',
            'Department.forceDelete',
            'Employee.viewAny',
            'Employee.view',
            'Employee.create',
            'Employee.update',
            'Employee.delete',
            'Employee.forceDelete',
        ],

        RolesEnum::Manager->value => [
            'Department.viewAny',
            'Department.view',
            'Department.create',
            'Department.update',
            'Employee.viewAny',
            'Employee.view',
            'Employee.create',
        ],

        RolesEnum::Supervisor->value => [
            'Department.viewAny',
            'Department.view',
            'Employee.viewAny',
            'Employee.view',
        ],

        RolesEnum::Staff->value => [
            'Employee.view',
        ],
    ],
];
