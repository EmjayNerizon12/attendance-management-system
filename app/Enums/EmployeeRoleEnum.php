<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum EmployeeRoleEnum: string implements HasLabel,HasColor,HasIcon
{
    const DEFAULT = self::Staff;
    case Manager = "manager";
    case Supervisor = "supervisor";
    case Staff = "staff";
    case Admin = "admin";

    public function getLabel(): string
    {
        return match ($this) {
            self::Manager => 'Manager',
            self::Supervisor => 'Supervisor',
            self::Staff => 'Staff',
            self::Admin => 'Admin',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::Manager => 'danger',
            self::Supervisor => 'warning',
            self::Staff => 'success',
            self::Admin => 'primary',
        };
    }
    public function getIcon():string
    {
        return match ($this) {
            self::Manager => 'heroicon-o-shield-exclamation',
            self::Supervisor => 'heroicon-o-shield-check',
            self::Staff => 'heroicon-o-user',
            self::Admin => 'heroicon-o-cog',
        };
    }
}
