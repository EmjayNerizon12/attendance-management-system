<?php

namespace App\Filament\Resources\ClockIns\Pages;

use App\Filament\Resources\ClockIns\ClockInResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClockIn extends ViewRecord
{
    protected static string $resource = ClockInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
