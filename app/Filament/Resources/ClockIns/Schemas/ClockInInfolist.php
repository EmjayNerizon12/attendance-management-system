<?php

namespace App\Filament\Resources\ClockIns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClockInInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('employee_id'),
                TextEntry::make('started_at')
                    ->dateTime()
                    ->timezone(config('app.timezone'))
                    ->placeholder('-'),
                TextEntry::make('note')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->timezone(config('app.timezone'))
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->timezone(config('app.timezone'))
                    ->placeholder('-'),
            ]);
    }
}
