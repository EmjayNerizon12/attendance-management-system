<?php

namespace App\Filament\Resources\ClockIns\Pages;

use App\Filament\Resources\ClockIns\ClockInResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClockIns extends ListRecords
{
    protected static string $resource = ClockInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
