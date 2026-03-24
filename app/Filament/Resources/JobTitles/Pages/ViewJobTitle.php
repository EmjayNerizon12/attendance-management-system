<?php

namespace App\Filament\Resources\JobTitles\Pages;

use App\Filament\Resources\JobTitles\JobTitleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJobTitle extends ViewRecord
{
    protected static string $resource = JobTitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
