<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\Package;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class MonthlyReportWidget extends ChartWidget
{
    protected static ?string $heading = '30 days';
    protected static string $chartType = 'line';

    public function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $labels = collect(range(29, 0))->map(function ($i) {
            return Carbon::now()->subDays($i)->format('m/d');
        });

        return  [
            'labels' => $labels->values()->all(),
            'datasets' => [
                [
                    'label' => 'Pending Orders',
                    'data' => $this->countByStatus(Order::class, 'pending', $labels),
                    'borderColor' => '#F59E0B',                  // Amber 500
                    'backgroundColor' => 'rgba(245,158,11,0.15)',// Amber 500 @ 15%
                    'pointBackgroundColor' => '#F59E0B',
                    'pointBorderColor' => '#F59E0B',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'fill' => true,
                ],
                [
                    'label' => 'Cancelled Orders',
                    'data' => $this->countByStatus(Order::class, 'cancelled', $labels),
                    'borderColor' => '#EF4444',                   // Rose/Red 500
                    'backgroundColor' => 'rgba(239,68,68,0.15)', // Red 500 @ 15%
                    'pointBackgroundColor' => '#EF4444',
                    'pointBorderColor' => '#EF4444',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'fill' => true,
                ],
                [
                    'label' => 'Processing Orders',
                    'data' => $this->countByStatus(Order::class, 'processing', $labels),
                    'borderColor' => '#6366F1',                   // Indigo 500
                    'backgroundColor' => 'rgba(99,102,241,0.15)',// Indigo 500 @ 15%
                    'pointBackgroundColor' => '#6366F1',
                    'pointBorderColor' => '#6366F1',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'fill' => true,
                ],
                [
                    'label' => 'Completed Orders',
                    'data' => $this->countByStatus(Order::class, 'completed', $labels),
                    'borderColor' => '#22C55E',                   // Indigo 500
                    'backgroundColor' => 'rgba(99,102,241,0.15)',// Indigo 500 @ 15%
                    'pointBackgroundColor' => '#22C55E',
                    'pointBorderColor' => '#22C55E',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                    'fill' => true,
                ],
            ],
        ];
    }

    protected function countByStatus(string $modelClass, string $status, Collection $labels): array
    {
        $user = auth()->user();

        return $labels->map(function ($label) use ($modelClass, $status, $user) {
            $date = Carbon::createFromFormat('m/d', $label)->setYear(now()->year);
            $query = $modelClass::whereDate('created_at', $date)
                ->where('status', $status);

            if ($user->role !='admin') {
                $query->where('user_id', $user->id);
            }

            return $query->count();
        })->all();
    }
}
