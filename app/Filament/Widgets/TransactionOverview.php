<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TransactionOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $totalIncome = Transaction::whereHas('category', fn($query) => $query->where('type', 'income'))->sum('amount');

        $totalExpanse = Transaction::whereHas('category', fn($query) => $query->where('type', 'expense'))->sum('amount');

        $balance = $totalIncome - $totalExpanse;
        return [
            Stat::make('Total Transaction', Transaction::count())
                ->description('Transaction')
                ->descriptionicon('heroicon-o-banknotes')
                ->color('secondary'),
            Stat::make('Total Income', 'Rp ' . number_format(Transaction::whereHas('category', fn($query) => $query->where('type', 'income'))->sum('amount'), 0, ',', '.'))
                ->description('Income')
                ->descriptionicon('heroicon-o-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Expense', 'Rp ' . number_format(Transaction::whereHas('category', fn($query) => $query->where('type', 'expense'))->sum('amount'), 0, ',', '.'))
                ->description('Expense')
                ->descriptionicon('heroicon-o-arrow-trending-down')
                ->color('danger'),
            Stat::make('Remaining Balance', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description('Balance')
                ->descriptionicon('heroicon-o-wallet')
                ->color('primary'),

            // Stat::make('Average time on page', '3:12')
            //     ->description('3% increase')
            //     ->descriptionicon('heroicon-m-arrow-trending-up'),
        ];
    }
}
