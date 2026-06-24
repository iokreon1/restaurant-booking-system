<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return array{
     *     summary: array{
     *         bookings_today: int,
     *         bookings_today_change_percent: float|null,
     *         pending_approval: int,
     *         occupied_tables: int,
     *         total_tables: int,
     *         occupancy_percent: int,
     *         cancellations_today: int,
     *         cancellations_delta_vs_yesterday: int,
     *     },
     *     pending_bookings: Collection<int, Booking>,
     *     trend_14_days: array<int, array{date: string, label: string, incoming: int, completed: int}>,
     *     trend_max: int,
     *     monthly_reservations: array{
     *         total: int,
     *         segments: array<int, array{key: string, label: string, count: int, percent: float, color: string}>,
     *         stroke_segments: array<int, array{key: string, label: string, count: int, percent: float, color: string, dasharray: string, dashoffset: float}>,
     *     },
     *     footer: array{
     *         revenue_today: float,
     *         revenue_change_percent: float|null,
     *         menu_active_count: int,
     *         menu_soldout_count: int,
     *         customer_total: int,
     *         customers_new_this_month: int,
     *     },
     *     recent_activity: array<int, array{type: string, title: string, description: string, icon: string, time_ago: string}>,
     * }
     */
    public function getDashboardData(?CarbonInterface $now = null): array
    {
        $now ??= now();

        $trend14Days = $this->buildBookingTrend14Days($now);
        $trendHeights = array_map(
            static fn (array $row): int => max($row['incoming'], $row['completed']),
            $trend14Days,
        );
        $trendMax = $trendHeights === [] ? 1 : max(1, ...$trendHeights);

        return [
            'summary' => $this->buildSummary($now),
            'pending_bookings' => $this->pendingQueueBookings($now),
            'trend_14_days' => $trend14Days,
            'trend_max' => $trendMax,
            'monthly_reservations' => $this->buildMonthlyReservationBreakdown($now),
            'footer' => $this->buildFooterMetrics($now),
            'recent_activity' => $this->buildRecentActivity($now),
        ];
    }

    /**
     * @return array{
     *     bookings_today: int,
     *     bookings_today_change_percent: float|null,
     *     pending_approval: int,
     *     occupied_tables: int,
     *     total_tables: int,
     *     occupancy_percent: int,
     *     cancellations_today: int,
     *     cancellations_delta_vs_yesterday: int,
     * }
     */
    public function buildSummary(CarbonInterface $now): array
    {
        $todayStr = $now->toDateString();
        $yesterdayStr = $now->copy()->subDay()->toDateString();

        $bookingsToday = Booking::query()->whereDate('booking_date', $todayStr)->count();
        $bookingsYesterday = Booking::query()->whereDate('booking_date', $yesterdayStr)->count();

        $pendingApproval = Booking::query()
            ->where(function ($query): void {
                $query->where('booking_status', Booking::BOOKING_STATUS_PENDING)
                    ->orWhere('payment_status', Booking::PAYMENT_STATUS_PENDING);
            })
            ->whereNotIn('booking_status', [
                Booking::BOOKING_STATUS_CANCELLED,
                Booking::BOOKING_STATUS_COMPLETED,
                Booking::BOOKING_STATUS_NO_SHOW,
            ])
            ->count();

        $totalTables = Table::query()
            ->whereNotIn('status', [Table::STATUS_MAINTENANCE, Table::STATUS_INACTIVE])
            ->count();

        $occupiedTables = Table::query()->where('status', Table::STATUS_BOOKED)->count();

        $occupancyPercent = $totalTables > 0
            ? (int) round(($occupiedTables / $totalTables) * 100)
            : 0;

        $cancellationsToday = Booking::query()
            ->where('booking_status', Booking::BOOKING_STATUS_CANCELLED)
            ->whereDate('updated_at', $todayStr)
            ->count();

        $cancellationsYesterday = Booking::query()
            ->where('booking_status', Booking::BOOKING_STATUS_CANCELLED)
            ->whereDate('updated_at', $yesterdayStr)
            ->count();

        return [
            'bookings_today' => $bookingsToday,
            'bookings_today_change_percent' => $this->percentChange($bookingsToday, $bookingsYesterday),
            'pending_approval' => $pendingApproval,
            'occupied_tables' => $occupiedTables,
            'total_tables' => $totalTables,
            'occupancy_percent' => $occupancyPercent,
            'cancellations_today' => $cancellationsToday,
            'cancellations_delta_vs_yesterday' => $cancellationsToday - $cancellationsYesterday,
        ];
    }

    /**
     * @return Collection<int, Booking>
     */
    public function pendingQueueBookings(CarbonInterface $now, int $limit = 5): Collection
    {
        return Booking::query()
            ->with(['user', 'table'])
            ->where(function ($query): void {
                $query->where('booking_status', Booking::BOOKING_STATUS_PENDING)
                    ->orWhere('payment_status', Booking::PAYMENT_STATUS_PENDING);
            })
            ->whereNotIn('booking_status', [
                Booking::BOOKING_STATUS_CANCELLED,
                Booking::BOOKING_STATUS_COMPLETED,
                Booking::BOOKING_STATUS_NO_SHOW,
            ])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array{date: string, label: string, incoming: int, completed: int}>
     */
    public function buildBookingTrend14Days(CarbonInterface $now): array
    {
        $rows = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $dateStr = $day->toDateString();

            $incoming = Booking::query()->whereDate('booking_date', $dateStr)->count();
            $completed = Booking::query()
                ->where('booking_status', Booking::BOOKING_STATUS_COMPLETED)
                ->whereDate('booking_date', $dateStr)
                ->count();

            $rows[] = [
                'date' => $dateStr,
                'label' => $day->translatedFormat('d M'),
                'incoming' => $incoming,
                'completed' => $completed,
            ];
        }

        return $rows;
    }

    /**
     * @return array{
     *     total: int,
     *     segments: array<int, array{key: string, label: string, count: int, percent: float, color: string}>,
     *     stroke_segments: array<int, array{key: string, label: string, count: int, percent: float, color: string, dasharray: string, dashoffset: float}>,
     * }
     */
    public function buildMonthlyReservationBreakdown(CarbonInterface $now): array
    {
        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        $counts = Booking::query()
            ->whereBetween('booking_date', [$start, $end])
            ->selectRaw('booking_status, count(*) as aggregate')
            ->groupBy('booking_status')
            ->pluck('aggregate', 'booking_status');

        $labels = [
            Booking::BOOKING_STATUS_CONFIRMED => 'Confirmed',
            Booking::BOOKING_STATUS_COMPLETED => 'Completed',
            Booking::BOOKING_STATUS_PENDING => 'Pending',
            Booking::BOOKING_STATUS_CANCELLED => 'Cancelled',
            Booking::BOOKING_STATUS_SEATED => 'Seated',
            Booking::BOOKING_STATUS_PREPARING => 'Preparing',
            Booking::BOOKING_STATUS_NO_SHOW => 'No show',
        ];

        $colors = [
            Booking::BOOKING_STATUS_CONFIRMED => '#025864',
            Booking::BOOKING_STATUS_COMPLETED => '#00D47E',
            Booking::BOOKING_STATUS_PENDING => '#F59E0B',
            Booking::BOOKING_STATUS_CANCELLED => '#EF4444',
            Booking::BOOKING_STATUS_SEATED => '#6366F1',
            Booking::BOOKING_STATUS_PREPARING => '#8B5CF6',
            Booking::BOOKING_STATUS_NO_SHOW => '#64748B',
        ];

        $total = (int) $counts->sum();

        $segments = [];

        foreach ($labels as $status => $label) {
            $count = (int) ($counts[$status] ?? 0);
            if ($count === 0) {
                continue;
            }
            $percent = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;
            $segments[] = [
                'key' => $status,
                'label' => $label,
                'count' => $count,
                'percent' => $percent,
                'color' => $colors[$status] ?? '#94A3B8',
            ];
        }

        return [
            'total' => $total,
            'segments' => $segments,
            'stroke_segments' => $this->donutStrokeSegments($segments),
        ];
    }

    /**
     * @param  array<int, array{key: string, label: string, count: int, percent: float, color: string}>  $segments
     * @return array<int, array{key: string, label: string, count: int, percent: float, color: string, dasharray: string, dashoffset: float}>
     */
    private function donutStrokeSegments(array $segments): array
    {
        $cumulative = 0.0;
        $out = [];

        foreach ($segments as $segment) {
            $p = $segment['percent'];
            $out[] = array_merge($segment, [
                'dasharray' => $p.' '.(100 - $p),
                'dashoffset' => 25 - $cumulative,
            ]);
            $cumulative += $p;
        }

        return $out;
    }

    /**
     * @return array{
     *     revenue_today: float,
     *     revenue_change_percent: float|null,
     *     menu_active_count: int,
     *     menu_soldout_count: int,
     *     customer_total: int,
     *     customers_new_this_month: int,
     * }
     */
    public function buildFooterMetrics(CarbonInterface $now): array
    {
        $todayStr = $now->toDateString();
        $yesterdayStr = $now->copy()->subDay()->toDateString();

        $revenueToday = (float) Transaction::query()
            ->where('status', Transaction::STATUS_SUCCESS)
            ->whereDate('paid_at', $todayStr)
            ->sum('amount');

        $revenueYesterday = (float) Transaction::query()
            ->where('status', Transaction::STATUS_SUCCESS)
            ->whereDate('paid_at', $yesterdayStr)
            ->sum('amount');

        return [
            'revenue_today' => $revenueToday,
            'revenue_change_percent' => $this->percentChange($revenueToday, $revenueYesterday),
            'menu_active_count' => MenuItem::query()->where('status', MenuItem::STATUS_AVAILABLE)->count(),
            'menu_soldout_count' => MenuItem::query()->where('status', MenuItem::STATUS_SOLDOUT)->count(),
            'customer_total' => User::query()->count(),
            'customers_new_this_month' => User::query()
                ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
                ->count(),
        ];
    }

    /**
     * @return array<int, array{type: string, title: string, description: string, icon: string, time_ago: string}>
     */
    public function buildRecentActivity(CarbonInterface $now, int $limit = 5): array
    {
        $bookings = Booking::query()
            ->with(['user', 'table'])
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        $activities = [];

        foreach ($bookings as $booking) {
            $activities[] = $this->mapBookingToActivity($booking, $now);
        }

        return $activities;
    }

    /**
     * @return array{type: string, title: string, description: string, icon: string, time_ago: string}
     */
    private function mapBookingToActivity(Booking $booking, CarbonInterface $now): array
    {
        $name = $booking->user?->name ?? 'Tamu';
        $tableLabel = $booking->table
            ? 'Meja #'.$booking->table->table_number
            : 'Meja —';

        if ($booking->booking_status === Booking::BOOKING_STATUS_CANCELLED) {
            return [
                'type' => 'cancelled',
                'title' => 'Booking Dibatalkan',
                'description' => $name.' — '.$tableLabel,
                'icon' => 'close',
                'time_ago' => $booking->updated_at?->diffForHumans($now) ?? '',
            ];
        }

        if ($booking->booking_status === Booking::BOOKING_STATUS_COMPLETED) {
            return [
                'type' => 'completed',
                'title' => 'Reservasi Selesai',
                'description' => $tableLabel.' ('.$name.')',
                'icon' => 'check',
                'time_ago' => $booking->updated_at?->diffForHumans($now) ?? '',
            ];
        }

        $createdAt = $booking->created_at;
        $updatedAt = $booking->updated_at;
        $isFresh = $createdAt && $updatedAt && abs($createdAt->diffInSeconds($updatedAt)) <= 2;

        if ($isFresh) {
            $timePart = is_string($booking->booking_time)
                ? substr($booking->booking_time, 0, 5)
                : (string) $booking->booking_time;

            return [
                'type' => 'new',
                'title' => 'Booking Baru',
                'description' => $name.' — '.$booking->booking_date?->format('d M').' '.$timePart,
                'icon' => 'add',
                'time_ago' => $createdAt->diffForHumans($now),
            ];
        }

        return [
            'type' => 'update',
            'title' => 'Pembaruan Booking',
            'description' => $name.' — '.$tableLabel,
            'icon' => 'add',
            'time_ago' => $booking->updated_at?->diffForHumans($now) ?? '',
        ];
    }

    private function percentChange(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
