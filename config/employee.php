<?php

use App\Enums\RolesEnum;

return [
    'timezone' => env('APP_TIMEZONE', config('app.timezone')),
    'permissions' => [
        RolesEnum::Admin->value => [
            '*'
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
