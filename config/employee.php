<?php
return [
    'permissions' => [
        'super-admin' => ['*'],

        'manager' => [
            'departments.viewAny',
            'departments.view',
            'departments.create',
            'departments.update',
            'employees.viewAny',
            'employees.view',
            'employees.create',
        ],

        'supervisor' => [
            'departments.viewAny',
            'departments.view',
            'employees.viewAny',
            'employees.view',
        ],

        'staff' => [
            'employees.view',
        ],
    ],
];
