<?php

namespace App\Filament\Resources\ClockIns\Pages;

use App\Filament\Resources\ClockIns\ClockInResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditClockIn extends EditRecord
{
    protected static string $resource = ClockInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
