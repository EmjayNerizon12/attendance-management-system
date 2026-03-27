<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmploymentTypeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fname')
                    ->label('First Name')
                    ->required(),
                TextInput::make('mname')
                    ->label('Middle Name'),
                TextInput::make('lname')
                    ->label('Last Name')
                    ->required(),
                TextInput::make('suffix')
                    ->label('Suffix'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Toggle::make('is_active')
                    ->label('Active'),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                Select::make('role')
                    ->label('Employee Role')
                    ->options(EmployeeRoleEnum::class)
                    ->default(EmployeeRoleEnum::Staff),
                Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('job_title_id')
                    ->label('Job Title')
                    ->relationship('jobTitle', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('employment_type')
                    ->label('Employment Type')
                    ->options(EmploymentTypeEnum::class)
                    ->default(EmploymentTypeEnum::FullTime)
                    ->required(),
                TextInput::make('address')
                    ->required(),
                DatePicker::make('hire_date'),
                TextInput::make('salary')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
