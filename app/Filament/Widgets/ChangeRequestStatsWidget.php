<?php

namespace App\Filament\Widgets;

use App\Models\ChangeRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChangeRequestStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingCount = ChangeRequest::where('status', 'pending')->count();
        $approvedToday = ChangeRequest::where('status', 'approved')
            ->whereDate('reviewed_at', today())->count();
        $totalThisWeek = ChangeRequest::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        return [
            Stat::make('Pending Requests', $pendingCount)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 10 ? 'danger' : ($pendingCount > 5 ? 'warning' : 'success'))
                ->url('/admin/change-requests?tableFilters[status][value]=pending'),

            Stat::make('Approved Today', $approvedToday)
                ->description('Approved in the last 24 hours')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('This Week', $totalThisWeek)
                ->description('Total requests submitted')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }
}
