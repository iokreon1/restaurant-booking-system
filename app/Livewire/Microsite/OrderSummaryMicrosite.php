<?php

namespace App\Livewire\Microsite;

use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class OrderSummaryMicrosite extends Component
{
    private const RESERVATION_SESSION_KEY = 'microsite.reservation';

    protected BookingService $bookingService;

    #[Validate('required|string|max:100', as: 'nama pemesan', onUpdate: false)]
    public string $customerName = '';

    #[Validate('required|string|email|max:100', as: 'email', onUpdate: false)]
    public string $customerEmail = '';

    #[Validate('required|string|max:20|regex:/^[0-9+\-\s()]+$/', as: 'nomor telepon', onUpdate: false)]
    public string $customerPhone = '';

    #[Validate('nullable|string|max:500', as: 'catatan', onUpdate: false)]
    public string $notes = '';

    #[Validate('required|date', as: 'tanggal reservasi', onUpdate: false)]
    public string $reservationDate = '';

    #[Validate('required|date_format:H:i', as: 'waktu reservasi', onUpdate: false)]
    public string $reservationTime = '';

    #[Validate('required|integer|min:1', as: 'jumlah tamu', onUpdate: false)]
    public int $partySize = 0;

    #[Validate('required|integer', as: 'meja reservasi', onUpdate: false)]
    public ?int $selectedTableId = null;

    public string $tableNumber = '-';

    public string $tableLocationDescription = '-';

    #[Validate([
        'cartItems' => 'required|array|min:1',
        'cartItems.*.id' => 'required|integer',
        'cartItems.*.quantity' => 'required|integer|min:1',
    ], onUpdate: false)]
    public array $cartItems = [];

    public ?string $orderError = null;

    public function mount(): void
    {
        $reservation = session(self::RESERVATION_SESSION_KEY);

        $this->customerName = (string) data_get($reservation, 'customer_name', '');
        $this->customerEmail = (string) data_get($reservation, 'customer_email', '');
        $this->customerPhone = (string) data_get($reservation, 'customer_phone', '');
        $this->notes = (string) data_get($reservation, 'notes', '');
        $this->reservationDate = (string) data_get($reservation, 'reservation_date', '');
        $this->reservationTime = (string) data_get($reservation, 'reservation_time', '');
        $this->partySize = (int) data_get($reservation, 'party_size', 0);
        $selectedTableId = data_get($reservation, 'selected_table_id');
        $this->selectedTableId = $selectedTableId !== null ? (int) $selectedTableId : null;
        $this->tableNumber = (string) data_get($reservation, 'table_number', '-');
        $this->tableLocationDescription = (string) data_get($reservation, 'table_location_description', '-');
    }

    public function boot(BookingService $bookingService): void
    {
        $this->bookingService = $bookingService;
    }

    #[Computed]
    public function reservationSummary(): ?array
    {
        if ($this->reservationDate === '') {
            return null;
        }

        $reservationDate = CarbonImmutable::parse($this->reservationDate)
            ->locale('id')
            ->translatedFormat('l, j F Y');

        return [
            'customer_name' => $this->customerName !== '' ? $this->customerName : '-',
            'customer_email' => $this->customerEmail !== '' ? $this->customerEmail : '-',
            'customer_phone' => $this->customerPhone !== '' ? $this->customerPhone : '-',
            'notes' => $this->notes !== '' ? $this->notes : '-',
            'date_label' => $reservationDate,
            'time_label' => $this->reservationTime !== '' ? $this->reservationTime.' WIB' : '-',
            'party_size_label' => $this->partySize > 0 ? $this->partySize.' Orang' : '-',
            'table_label' => 'Meja '.$this->tableNumber,
            'location_label' => $this->tableLocationDescription,
        ];
    }

    /**
     * @param  array<int, array{id?: int|string, quantity?: int|string}>  $cartItems
     */
    public function confirmOrder(array $cartItems)
    {
        $this->resetErrorBag();
        $this->orderError = null;

        $this->cartItems = $cartItems;

        $validated = $this->validate();

        $checkout = $this->bookingService->createBooking([
            'customer_name' => $validated['customerName'],
            'customer_email' => $validated['customerEmail'],
            'customer_phone' => $validated['customerPhone'],
            'note' => trim($validated['notes']),
            'reservation_date' => $validated['reservationDate'],
            'reservation_time' => $validated['reservationTime'],
            'party_size' => $validated['partySize'],
            'selected_table_id' => $validated['selectedTableId'],
        ], $validated['cartItems']);

        session()->forget(self::RESERVATION_SESSION_KEY);

        return redirect()->away($checkout['redirect_url']);
    }

    public function render()
    {
        return view('livewire.microsite.order-summary')
            ->layout('layouts.microsite')
            ->title('Ringkasan Order | The Organic Atelier');
    }
}
