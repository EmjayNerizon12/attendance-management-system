<?php

namespace App\Filament\Resources\ClockIns\RelationManagers;

use App\Filament\Resources\ClockIns\ClockInResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ClockOutRelationManager extends RelationManager
{
    protected static string $relationship = 'ClockOut';

    protected static ?string $relatedResource = ClockInResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
