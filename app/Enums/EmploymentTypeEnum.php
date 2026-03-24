<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EmploymentTypeEnum: string implements HasLabel
{
    case FullTime = 'full_time';
    case Contractor = 'contractor';
    case Intern = 'intern';
    case PartTime = 'part_time';

    public function getLabel(): string
    {
        return match ($this) {
            self::FullTime => 'Full Time',
            self::Contractor => 'Contractor',
            self::Intern => 'Intern',
            self::PartTime => 'Part Time',
        };
    }
}
