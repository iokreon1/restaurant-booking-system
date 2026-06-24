<?php

namespace App\Livewire\Admin\Booking;

use App\Livewire\Concerns\InteractsWithDashboardAlert;
use App\Models\Booking;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class PaymentStatusModal extends Component
{
    use InteractsWithDashboardAlert;

    public Booking $booking;

    public bool $showModal = false;

    public string $payment_status = '';

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
        $this->payment_status = $booking->payment_status;
    }

    public function openModal(): void
    {
        $this->resetValidation('payment_status');
        $this->payment_status = $this->booking->fresh()->payment_status;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->resetValidation('payment_status');
        $this->payment_status = $this->booking->payment_status;
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate([
            'payment_status' => ['required', 'string', Rule::in($this->paymentStatusKeys())],
        ]);

        $this->booking->update([
            'payment_status' => $this->payment_status,
        ]);

        $this->booking->refresh();
        $this->payment_status = $this->booking->payment_status;
        $this->showModal = false;

        $this->dispatch('booking-updated', bookingId: $this->booking->id);

        $this->dashboardAlertSuccess(
            'Berhasil',
            'Status pembayaran telah diperbarui.',
        );
    }

    public function render(): View
    {
        return view('livewire.admin.booking.payment-status-modal', [
            'paymentStatuses' => [
                Booking::PAYMENT_STATUS_PENDING => 'Menunggu bayar',
                Booking::PAYMENT_STATUS_PAID => 'Lunas',
                Booking::PAYMENT_STATUS_EXPIRED => 'Kedaluwarsa',
                Booking::PAYMENT_STATUS_REFUNDED => 'Dikembalikan',
            ],
        ]);
    }

    /**
     * @return list<string>
     */
    private function paymentStatusKeys(): array
    {
        return [
            Booking::PAYMENT_STATUS_PENDING,
            Booking::PAYMENT_STATUS_PAID,
            Booking::PAYMENT_STATUS_EXPIRED,
            Booking::PAYMENT_STATUS_REFUNDED,
        ];
    }
}
