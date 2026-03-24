<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Enums\EmployeeRoleEnum;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(static fn (): int => EmployeeResource::getEloquentQuery()->count())
                ->deferBadge(),
            EmployeeRoleEnum::Manager->value => $this->makeRoleTab(EmployeeRoleEnum::Manager),
            EmployeeRoleEnum::Supervisor->value => $this->makeRoleTab(EmployeeRoleEnum::Supervisor),
            EmployeeRoleEnum::Staff->value => $this->makeRoleTab(EmployeeRoleEnum::Staff),
            EmployeeRoleEnum::Admin->value => $this->makeRoleTab(EmployeeRoleEnum::Admin),
        ];
    }

    protected function makeRoleTab(EmployeeRoleEnum $role): Tab
    {
        return Tab::make($role->getLabel())
            ->badge(static fn (): int => Employee::query()->where('role', $role)->count())
            ->deferBadge()
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('role', $role));
    }
}
