<?php

namespace App\Filament\Resources\ClockIns;

use App\Filament\Resources\ClockIns\Pages\CreateClockIn;
use App\Filament\Resources\ClockIns\Pages\EditClockIn;
use App\Filament\Resources\ClockIns\Pages\ListClockIns;
use App\Filament\Resources\ClockIns\Pages\ViewClockIn;
use App\Filament\Resources\ClockIns\Schemas\ClockInForm;
use App\Filament\Resources\ClockIns\Schemas\ClockInInfolist;
use App\Filament\Resources\ClockIns\Tables\ClockInsTable;
use App\Models\ClockIn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClockInResource extends Resource
{
    protected static ?string $model = ClockIn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    public static function form(Schema $schema): Schema
    {
        return ClockInForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClockInInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClockInsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClockIns::route('/'),
            'create' => CreateClockIn::route('/create'),
            'view' => ViewClockIn::route('/{record}'),
            'edit' => EditClockIn::route('/{record}/edit'),
        ];
    }
}
