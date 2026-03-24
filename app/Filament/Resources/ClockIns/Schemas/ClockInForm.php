<?php

namespace App\Filament\Resources\ClockIns\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClockInForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();

        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee')
                    ->getOptionLabelFromRecordUsing(fn(Employee $record): string => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn() => $user?->employee?->id)
                    ->selectablePlaceholder(false),
                DateTimePicker::make('started_at')
                    ->timezone(config('app.timezone')),
                TextInput::make('note'),
            ]);
    }
}
