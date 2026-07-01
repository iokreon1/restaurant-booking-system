<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\MenuItem;
use App\Services\BookingService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class BookingEditPage extends Component
{
    public Booking $booking;

    /**
     * @var list<array{id: int, quantity: int}>
     */
    public array $cartItems = [];

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['user', 'table']);
        $this->cartItems = [];
        foreach ($booking->items ?? [] as $row) {
            $id = (int) data_get($row, 'menu_item_id', data_get($row, 'id'));
            if ($id < 1) {
                continue;
            }
            $this->cartItems[] = [
                'id' => $id,
                'quantity' => max(1, (int) data_get($row, 'quantity', 1)),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
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
            $bookingService->updateBookingItems($this->booking, $validated['cartItems']);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $message) {
                    $this->addError($key, $message);
                }
            }

            return;
        }

        session()->flash('status', 'Pesanan diperbarui.');
        $this->redirectRoute('admin.bookings.show', $this->booking);
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

        return view('livewire.admin.booking-edit-page', [
            'menuItems' => $menuItems,
            'orderLines' => $orderLines,
            'cartTotal' => $cartTotal,
        ])
            ->layout('layouts.dashboard')
            ->title('Ubah pesanan | Empon Pawon');
    }
}
