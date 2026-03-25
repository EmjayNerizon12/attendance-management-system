<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Enums\EmployeeRoleEnum;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return EmployeeResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn (): int => $this->getFilteredTabBadgeCount()),
            EmployeeRoleEnum::Manager->value => $this->makeRoleTab(EmployeeRoleEnum::Manager),
            EmployeeRoleEnum::Supervisor->value => $this->makeRoleTab(EmployeeRoleEnum::Supervisor),
            EmployeeRoleEnum::Staff->value => $this->makeRoleTab(EmployeeRoleEnum::Staff),
            EmployeeRoleEnum::Admin->value => $this->makeRoleTab(EmployeeRoleEnum::Admin),
        ];
    }

    protected function makeRoleTab(EmployeeRoleEnum $role): Tab
    {
        return Tab::make($role->getLabel())
            ->badge(fn (): int => $this->getFilteredTabBadgeCount($role))
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('role', $role));
    }

    protected function getFilteredTabBadgeCount(?EmployeeRoleEnum $role = null): int
    {
        $query = EmployeeResource::getEloquentQuery();

        $this->filterTableQuery($query);

        if ($role) {
            $query->where('role', $role);
        }

        return $query->count();
    }
}
