<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Employee;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStats extends StatsOverviewWidget
{
    use InteractsWithPageTable;
    protected function getTablePage():string{
        return ListEmployees::class;
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Total Employee', $this->getPageTableQuery()->count())
        ];
    }
}
