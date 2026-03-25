<?php

namespace App\Filament\Widgets;

use App\Models\ClockIn;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class LatestClockInWidget extends ChartWidget
{
    protected ?string $heading = 'Clock Ins - Last 30 Days';

    protected function getData(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $clockIns = ClockIn::query()
            ->selectRaw('DATE(started_at) as date, COUNT(*) as total')
            ->whereBetween('started_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(started_at)'))
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $day = $date->format('Y-m-d');

            $labels[] = $date->format('M d');
            $data[] = $clockIns[$day] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Clock Ins',
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
