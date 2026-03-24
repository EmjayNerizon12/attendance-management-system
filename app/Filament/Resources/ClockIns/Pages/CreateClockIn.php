<?php

namespace App\Filament\Resources\ClockIns\Pages;

use App\Enums\RolesEnum;
use App\Filament\Resources\ClockIns\ClockInResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClockIn extends CreateRecord
{
    protected static string $resource = ClockInResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user && (! $user->hasRole(RolesEnum::Admin->value))) {
            $data['employee_id'] = $user->employee?->id;
        }

        return $data;
    }
}
