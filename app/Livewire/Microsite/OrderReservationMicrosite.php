<?php

namespace App\Livewire\Microsite;

use App\Models\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrderReservationMicrosite extends Component
{
    private const RESERVATION_SESSION_KEY = 'microsite.reservation';

    public string $customerName = '';

    public string $customerEmail = '';

    public string $customerPhone = '';

    public string $notes = '';

    public string $reservationDate = '';

    public string $reservationTime = '19:00';

    public int $partySize = 2;

    public string $selectedArea = 'indoor';

    public ?int $selectedTableId = null;

    public function mount(): void
    {
        /** @var array{
         *     customer_name?: string,
         *     customer_email?: string,
         *     customer_phone?: string,
         *     notes?: string,
         *     reservation_date?: string,
         *     reservation_time?: string,
         *     party_size?: int,
         *     selected_area?: string,
         *     selected_table_id?: int
         * }|null $savedReservation
         */
        $savedReservation = session(self::RESERVATION_SESSION_KEY);

        $this->customerName = (string) data_get($savedReservation, 'customer_name', '');
        $this->customerEmail = (string) data_get($savedReservation, 'customer_email', '');
        $this->customerPhone = (string) data_get($savedReservation, 'customer_phone', '');
        $this->notes = (string) data_get($savedReservation, 'notes', '');
        $this->reservationDate = data_get($savedReservation, 'reservation_date', now()->toDateString());
        $this->reservationTime = data_get($savedReservation, 'reservation_time', '19:00');
        $this->partySize = (int) data_get($savedReservation, 'party_size', 2);
        $this->selectedArea = (string) data_get($savedReservation, 'selected_area', 'indoor');
        $savedTableId = data_get($savedReservation, 'selected_table_id');
        $this->selectedTableId = $savedTableId !== null ? (int) $savedTableId : null;

        $this->ensureSelectedTable();
    }

    public function updatedSelectedArea(): void
    {
        $this->ensureSelectedTable();
    }

    public function updatedPartySize(): void
    {
        $this->ensureSelectedTable();
    }

    #[Computed]
    public function tables(): array
    {
        return Table::query()
            ->where('status', Table::STATUS_AVAILABLE)
            ->where('capacity', '>=', $this->partySize)
            ->orderBy('table_number')
            ->get(['id', 'table_number', 'capacity', 'location_description'])
            ->map(function (Table $table): array {
                $location = strtolower($table->location_description);
                $area = Str::contains($location, 'outdoor') ? 'outdoor' : 'indoor';

                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'location_description' => $table->location_description,
                    'area' => $area,
                ];
            })
            ->filter(fn (array $table): bool => $table['area'] === $this->selectedArea)
            ->values()
            ->all();
    }

    private function ensureSelectedTable(): void
    {
        $availableTableIds = collect($this->tables)->pluck('id');

        if ($this->selectedTableId && $availableTableIds->contains($this->selectedTableId)) {
            return;
        }

        $this->selectedTableId = $availableTableIds->first();
    }

    public function proceedToSummary()
    {
        $validated = $this->validate([
            'customerName' => ['required', 'string', 'max:100'],
            'customerEmail' => ['required', 'string', 'email', 'max:100'],
            'customerPhone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'notes' => ['nullable', 'string', 'max:500'],
            'reservationDate' => ['required', 'date'],
            'reservationTime' => ['required', 'date_format:H:i'],
            'partySize' => ['required', 'integer', 'min:1', 'max:12'],
            'selectedArea' => ['required', 'in:indoor,outdoor'],
            'selectedTableId' => ['required', 'integer'],
        ]);

        $selectedTable = collect($this->tables)
            ->firstWhere('id', $validated['selectedTableId']);

        if (! is_array($selectedTable)) {
            throw ValidationException::withMessages([
                'selectedTableId' => 'Meja yang dipilih tidak tersedia lagi.',
            ]);
        }

        session([
            self::RESERVATION_SESSION_KEY => [
                'customer_name' => $validated['customerName'],
                'customer_email' => $validated['customerEmail'],
                'customer_phone' => $validated['customerPhone'],
                'notes' => trim((string) $validated['notes']),
                'reservation_date' => $validated['reservationDate'],
                'reservation_time' => $validated['reservationTime'],
                'party_size' => $validated['partySize'],
                'selected_area' => $validated['selectedArea'],
                'selected_table_id' => $validated['selectedTableId'],
                'table_number' => $selectedTable['table_number'],
                'table_capacity' => $selectedTable['capacity'],
                'table_location_description' => $selectedTable['location_description'],
            ],
        ]);

        return redirect()->route('microsite.summary');
    }

    public function render()
    {
        return view('livewire.microsite.order-reservation')
            ->layout('layouts.microsite')
            ->title('Reservasi Meja | The Organic Atelier');
    }
}
