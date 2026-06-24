<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class BookingCreatePage extends Component
{
    public int $user_id = 0;

    public int $table_id = 0;

    public string $booking_date = '';

    public string $booking_time = '';

    public int $guest_count = 2;

    public string $booking_status = Booking::BOOKING_STATUS_PENDING;

    public string $payment_status = Booking::PAYMENT_STATUS_PENDING;

    public string $note = '';

    public string $cancellation_reason = '';

    /**
     * @var list<array{id: int, quantity: int}>
     */
    public array $cartItems = [];

    public function mount(): void
    {
        $this->booking_date = now()->format('Y-m-d');
        $this->booking_time = '19:00';
        $this->user_id = (int) (User::query()->value('id') ?? 0);
        $this->table_id = (int) (Table::query()->value('id') ?? 0);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'table_id' => ['required', 'exists:tables,id'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:500'],
            'booking_status' => ['required', 'string'],
            'payment_status' => ['required', 'string'],
            'note' => ['nullable', 'string'],
            'cancellation_reason' => ['nullable', 'string'],
            'cartItems' => ['required', 'array', 'min:1'],
            'cartItems.*.id' => [
                'required',
                'integer',
                Rule::exists('menu_items', 'id')->where('status', MenuItem::STATUS_AVAILABLE),
            ],
            'cartItems.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function addToCart(int $menuItemId): void
    {
        foreach ($this->cartItems as $idx => $row) {
            if ($row['id'] === $menuItemId) {
                $this->cartItems[$idx]['quantity']++;

                return;
            }
        }

        $this->cartItems[] = ['id' => $menuItemId, 'quantity' => 1];
    }

    public function incrementItem(int $menuItemId): void
    {
        $this->addToCart($menuItemId);
    }

    public function decrementItem(int $menuItemId): void
    {
        foreach ($this->cartItems as $idx => $row) {
            if ($row['id'] === $menuItemId) {
                $this->cartItems[$idx]['quantity']--;
                if ($this->cartItems[$idx]['quantity'] < 1) {
                    unset($this->cartItems[$idx]);
                    $this->cartItems = array_values($this->cartItems);
                }

                return;
            }
        }
    }

    public function save(BookingService $bookingService): void
    {
        $validated = $this->validate();

        try {
            $bookingService->createManualBooking(
                [
                    'user_id' => $validated['user_id'],
                    'table_id' => $validated['table_id'],
                    'booking_date' => $validated['booking_date'],
                    'booking_time' => $validated['booking_time'],
                    'guest_count' => $validated['guest_count'],
                    'booking_status' => $validated['booking_status'],
                    'payment_status' => $validated['payment_status'],
                    'note' => $validated['note'] ?? null,
                    'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                ],
                $validated['cartItems'],
            );
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $message) {
                    $this->addError($key, $message);
                }
            }

            return;
        }

        session()->flash('status', 'Booking ditambahkan.');
        $this->redirectRoute('admin.bookings');
    }

    /**
     * @return list<array{id: int, name: string, unit_price: float, quantity: int, subtotal: float}>
     */
    private function orderLinePreview(): array
    {
        if ($this->cartItems === []) {
            return [];
        }

        $ids = collect($this->cartItems)->pluck('id');
        $menuById = MenuItem::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'price'])
            ->keyBy('id');

        $lines = [];
        foreach ($this->cartItems as $row) {
            $menuItem = $menuById->get($row['id']);
            if ($menuItem === null) {
                continue;
            }
            $unitPrice = (float) $menuItem->price;
            $qty = $row['quantity'];
            $lines[] = [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $unitPrice * $qty,
            ];
        }

        return $lines;
    }

    public function render(): View
    {
        $menuItems = MenuItem::query()
            ->with(['category:id,name'])
            ->where('status', '!=', MenuItem::STATUS_INACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $orderLines = $this->orderLinePreview();
        $cartTotal = collect($orderLines)->sum('subtotal');

        $users = User::query()->orderBy('name')->get();
        $tables = Table::query()->orderBy('table_number')->get();

        return view('livewire.admin.booking-create-page', [
            'menuItems' => $menuItems,
            'orderLines' => $orderLines,
            'cartTotal' => $cartTotal,
            'users' => $users,
            'tables' => $tables,
            'bookingStatuses' => [
                Booking::BOOKING_STATUS_PENDING => 'Menunggu',
                Booking::BOOKING_STATUS_CONFIRMED => 'Dikonfirmasi',
                Booking::BOOKING_STATUS_SEATED => 'Duduk',
                Booking::BOOKING_STATUS_PREPARING => 'Menyiapkan',
                Booking::BOOKING_STATUS_COMPLETED => 'Selesai',
                Booking::BOOKING_STATUS_CANCELLED => 'Dibatalkan',
                Booking::BOOKING_STATUS_NO_SHOW => 'Tidak hadir',
            ],
            'paymentStatuses' => [
                Booking::PAYMENT_STATUS_PENDING => 'Menunggu bayar',
                Booking::PAYMENT_STATUS_PAID => 'Lunas',
                Booking::PAYMENT_STATUS_EXPIRED => 'Kedaluwarsa',
                Booking::PAYMENT_STATUS_REFUNDED => 'Dikembalikan',
            ],
        ])
            ->layout('layouts.dashboard')
            ->title('Booking baru | Dapur Nabilah');
    }
}
