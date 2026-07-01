<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\Table;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class BookingsPage extends Component
{
    use WithPagination;

    public function openCreate(): void
    {
        $this->redirectRoute('admin.bookings.create');
    }

    public function delete(int $id): void
    {
        Booking::query()->findOrFail($id)->delete();
        session()->flash('status', 'Booking dihapus.');
    }

    public function render(): View
    {
        $bookings = Booking::query()
            ->with(['user', 'table'])
            ->latest('created_at')
            ->where('payment_status', '=', Booking::PAYMENT_STATUS_PAID)
            ->paginate(10);

        $today = now()->toDateString();
        $stats = [
            'total' => Booking::query()->count(),
            'pending' => Booking::query()->where('booking_status', Booking::BOOKING_STATUS_PENDING)->count(),
            'today' => Booking::query()->whereDate('booking_date', $today)->count(),
        ];

        return view('livewire.admin.bookings-page', [
            'bookings' => $bookings,
            'canCreateBooking' => User::query()->exists() && Table::query()->exists(),
            'stats' => $stats,
        ])
            ->layout('layouts.dashboard')
            ->title('Booking | Empon Pawon');
    }
}
