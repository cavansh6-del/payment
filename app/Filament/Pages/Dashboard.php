<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MonthlyReportWidget;
use App\Filament\Widgets\QuickLinks;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $modelLabel = 'Dashboard';
    protected static ?string $pluralModelLabel = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-m-home';

    protected static ?int $navigationSort = -1;
    protected static string $view = 'filament.pages.dashboard';

    protected static string $routePath = '/';
    public static function getWidgets(): array
    {
        return [
            MonthlyReportWidget::class,
        ];
    }
}
