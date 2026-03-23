<?php

namespace App\Enums;

enum RolesEnum: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Supervisor = 'supervisor';
    case Staff = 'staff';
}
