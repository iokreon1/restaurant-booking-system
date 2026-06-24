<?php

namespace App\Livewire\Microsite;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

class OrderTrackingMicrosite extends Component
{
    #[Validate('required|string|max:50', as: 'order id', onUpdate: false)]
    public string $orderId = '';

    public bool $hasSearched = false;

    public function mount(): void
    {
        $orderIdFromQuery = request()->query('order_id');

        if (is_string($orderIdFromQuery) && $orderIdFromQuery !== '') {
            $this->orderId = $orderIdFromQuery;
            $this->searchOrder();
        }
    }

    /**
     * @var array{
     *     booking_reference: string,
     *     booking_status_label: string,
     *     booking_status_description: string,
     *     payment_status_label: string,
     *     transaction_status_label: string,
     *     date_label: string,
     *     time_label: string,
     *     table_label: string,
     *     guest_count_label: string,
     *     total_amount_label: string,
     *     note: string,
     *     items: array<int, array{name: string, quantity_label: string, subtotal_label: string}>
     * }|null
     */
    public ?array $bookingDetails = null;

    public function searchOrder(): void
    {
        $this->resetErrorBag();
        $this->bookingDetails = null;
        $this->hasSearched = true;

        $validated = $this->validate();
        $this->orderId = Str::upper(trim($validated['orderId']));

        $booking = Booking::query()
            ->with([
                'table:id,table_number,location_description',
                'transaction:id,booking_id,status',
            ])
            ->where('booking_reference', $this->orderId)
            ->first();

        if (! $booking instanceof Booking) {
            $this->addError('orderId', 'Order ID tidak ditemukan. Pastikan kode yang dimasukkan benar.');

            return;
        }

        $this->bookingDetails = $this->buildBookingDetails($booking);
    }

    /**
     * @return array{
     *     booking_reference: string,
     *     booking_status_label: string,
     *     booking_status_description: string,
     *     payment_status_label: string,
     *     transaction_status_label: string,
     *     date_label: string,
     *     time_label: string,
     *     table_label: string,
     *     guest_count_label: string,
     *     total_amount_label: string,
     *     note: string,
     *     items: array<int, array{name: string, quantity_label: string, subtotal_label: string}>
     * }
     */
    private function buildBookingDetails(Booking $booking): array
    {
        $bookingStatus = $this->bookingStatusMeta($booking->booking_status);

        return [
            'booking_reference' => $booking->booking_reference,
            'booking_status_label' => $bookingStatus['label'],
            'booking_status_description' => $bookingStatus['description'],
            'payment_status_label' => $this->paymentStatusLabel($booking->payment_status),
            'transaction_status_label' => $this->transactionStatusLabel(
                $booking->transaction?->status ?? $booking->payment_status,
            ),
            'date_label' => $booking->booking_date
                ->locale('id')
                ->translatedFormat('l, j F Y'),
            'time_label' => CarbonImmutable::parse((string) $booking->booking_time)->format('H:i').' WIB',
            'table_label' => $booking->table?->table_number !== null
                ? 'Meja '.$booking->table->table_number.' - '.$booking->table->location_description
                : '-',
            'guest_count_label' => $booking->guest_count.' Orang',
            'total_amount_label' => $this->formatRupiah((float) $booking->total_amount),
            'note' => $booking->note ?? '-',
            'items' => collect($booking->items)
                ->map(function (array $item): array {
                    $quantity = (int) data_get($item, 'quantity', 0);
                    $subtotal = (float) data_get($item, 'subtotal', 0);

                    return [
                        'name' => (string) data_get($item, 'name', '-'),
                        'quantity_label' => $quantity.'x',
                        'subtotal_label' => $this->formatRupiah($subtotal),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{label: string, description: string}
     */
    private function bookingStatusMeta(string $status): array
    {
        return match ($status) {
            Booking::BOOKING_STATUS_PENDING => [
                'label' => 'Menunggu Konfirmasi',
                'description' => 'Reservasi Anda sudah tercatat dan sedang menunggu konfirmasi restoran.',
            ],
            Booking::BOOKING_STATUS_CONFIRMED => [
                'label' => 'Booking Dikonfirmasi',
                'description' => 'Reservasi sudah dikonfirmasi. Silakan datang sesuai jadwal yang dipilih.',
            ],
            Booking::BOOKING_STATUS_SEATED => [
                'label' => 'Tamu Sudah Datang',
                'description' => 'Host sudah menerima kedatangan Anda dan meja sedang digunakan.',
            ],
            Booking::BOOKING_STATUS_PREPARING => [
                'label' => 'Meja Sedang Disiapkan',
                'description' => 'Tim kami sedang menyiapkan meja Anda sebelum waktu reservasi.',
            ],
            Booking::BOOKING_STATUS_COMPLETED => [
                'label' => 'Reservasi Selesai',
                'description' => 'Kunjungan untuk reservasi ini telah selesai.',
            ],
            Booking::BOOKING_STATUS_CANCELLED => [
                'label' => 'Reservasi Dibatalkan',
                'description' => 'Reservasi ini sudah dibatalkan.',
            ],
            Booking::BOOKING_STATUS_NO_SHOW => [
                'label' => 'Tidak Hadir',
                'description' => 'Reservasi ditandai tidak hadir oleh restoran.',
            ],
            default => [
                'label' => Str::headline($status),
                'description' => 'Status reservasi telah diperbarui.',
            ],
        };
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            Booking::PAYMENT_STATUS_PENDING => 'Menunggu Pembayaran',
            Booking::PAYMENT_STATUS_PAID => 'Pembayaran Berhasil',
            Booking::PAYMENT_STATUS_EXPIRED => 'Pembayaran Kedaluwarsa',
            Booking::PAYMENT_STATUS_REFUNDED => 'Pembayaran Dikembalikan',
            default => Str::headline($status),
        };
    }

    private function transactionStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Transaksi Menunggu Pembayaran',
            'success', 'paid' => 'Transaksi Berhasil',
            'failed' => 'Transaksi Gagal',
            'expired' => 'Transaksi Kedaluwarsa',
            'refunded' => 'Transaksi Dikembalikan',
            default => Str::headline($status),
        };
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp'.number_format($amount, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.microsite.order-tracking')
            ->layout('layouts.microsite', ['activeNav' => 'bookings'])
            ->title('Status Booking | The Organic Atelier');
    }
}
