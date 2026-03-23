<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                Select::make('role')
                    ->label('Employee Role')
                    ->options(\App\Enums\EmployeeRoleEnum::class)
                    ->default(\App\Enums\EmployeeRoleEnum::Staff),
                Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('address')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
