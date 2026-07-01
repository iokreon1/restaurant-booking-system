<?php

namespace App\Livewire\Admin\Booking;

use App\Helper\BookingStatusHelper;
use App\Livewire\Concerns\InteractsWithDashboardAlert;
use App\Models\Booking;
use App\Models\Transaction;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\View\View;
use Livewire\Component;

class BookingShowPage extends Component
{
    use InteractsWithDashboardAlert;

    public Booking $booking;

    public function mount(Booking $booking): void
    {
        $this->booking = $this->loadBookingRelations($booking);
    }

    public function updateBookingStatus(string $status): void
    {
        $nextStatuses = collect($this->bookingStatusActions())
            ->pluck('status')
            ->all();

        if (! in_array($status, $nextStatuses, true)) {
            $this->dashboardAlertWarning(
                'Tidak valid',
                'Transisi status pesanan ini tidak tersedia.',
            );

            return;
        }

        $this->booking->update([
            'booking_status' => $status,
        ]);

        $this->booking = $this->loadBookingRelations($this->booking->refresh());
        $this->sendBookingStatusNotification($status);

        $this->dashboardAlertSuccess(
            'Berhasil',
            'Status pesanan telah diperbarui.',
        );
    }

    public function render(): View
    {
        return view('livewire.admin.booking-show-page', [
            'bookingStatuses' => BookingStatusHelper::labels(),
            'bookingStatusActions' => $this->bookingStatusActions(),
            'paymentStatuses' => [
                Booking::PAYMENT_STATUS_PENDING => 'Menunggu bayar',
                Booking::PAYMENT_STATUS_PAID => 'Lunas',
                Booking::PAYMENT_STATUS_EXPIRED => 'Kedaluwarsa',
                Booking::PAYMENT_STATUS_REFUNDED => 'Dikembalikan',
            ],
            'transactionStatuses' => [
                Transaction::STATUS_PENDING => 'Menunggu',
                Transaction::STATUS_SUCCESS => 'Berhasil',
                Transaction::STATUS_FAILED => 'Gagal',
                Transaction::STATUS_EXPIRED => 'Kedaluwarsa',
            ],
        ])
            ->layout('layouts.dashboard')
            ->title('Detail booking | Empon Pawon');
    }

    private function loadBookingRelations(Booking $booking): Booking
    {
        return $booking->load(['user', 'table', 'transaction']);
    }

    private function sendBookingStatusNotification(string $status): void
    {
        if ($this->booking->user === null) {
            return;
        }

        if ($status === Booking::BOOKING_STATUS_CONFIRMED) {
            $this->booking->user->notify(new BookingConfirmedNotification($this->booking));

            return;
        }

        if ($status === Booking::BOOKING_STATUS_CANCELLED) {
            $this->booking->user->notify(new BookingCancelledNotification($this->booking));
        }
    }

    /**
     * @return list<array{label: string, status: string, class: string}>
     */
    private function bookingStatusActions(): array
    {
        return match ($this->booking->booking_status) {
            Booking::BOOKING_STATUS_PENDING => [
                [
                    'label' => 'Konfirmasi',
                    'status' => Booking::BOOKING_STATUS_CONFIRMED,
                    'class' => 'border-sky-200 bg-sky-100 text-sky-900 hover:bg-sky-200',
                ],
                [
                    'label' => 'Tolak',
                    'status' => Booking::BOOKING_STATUS_CANCELLED,
                    'class' => 'border-red-200 bg-red-100 text-red-900 hover:bg-red-200',
                ],
            ],
            Booking::BOOKING_STATUS_CONFIRMED => [
                [
                    'label' => 'Tamu Datang',
                    'status' => Booking::BOOKING_STATUS_SEATED,
                    'class' => 'border-cyan-200 bg-cyan-100 text-cyan-900 hover:bg-cyan-200',
                ],
                [
                    'label' => 'No Show',
                    'status' => Booking::BOOKING_STATUS_NO_SHOW,
                    'class' => 'border-slate-200 bg-slate-100 text-slate-800 hover:bg-slate-200',
                ],
                [
                    'label' => 'Batalkan',
                    'status' => Booking::BOOKING_STATUS_CANCELLED,
                    'class' => 'border-red-200 bg-red-100 text-red-900 hover:bg-red-200',
                ],
            ],
            Booking::BOOKING_STATUS_SEATED => [
                [
                    'label' => 'Mulai Siapkan',
                    'status' => Booking::BOOKING_STATUS_PREPARING,
                    'class' => 'border-violet-200 bg-violet-100 text-violet-900 hover:bg-violet-200',
                ],
                [
                    'label' => 'Selesaikan',
                    'status' => Booking::BOOKING_STATUS_COMPLETED,
                    'class' => 'border-emerald-200 bg-emerald-100 text-emerald-900 hover:bg-emerald-200',
                ],
            ],
            Booking::BOOKING_STATUS_PREPARING => [
                [
                    'label' => 'Selesaikan',
                    'status' => Booking::BOOKING_STATUS_COMPLETED,
                    'class' => 'border-emerald-200 bg-emerald-100 text-emerald-900 hover:bg-emerald-200',
                ],
            ],
            default => [],
        };
    }
}
