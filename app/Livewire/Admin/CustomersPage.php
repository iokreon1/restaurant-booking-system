<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersPage extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $base = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->withCount('bookings');

        $customers = (clone $base)
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $totalCustomers = (clone $base)->count();
        $withBookings = (clone $base)->has('bookings')->count();
        $newThisMonth = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();
        $verifiedCount = (clone $base)->whereNotNull('email_verified_at')->count();

        $stats = [
            'total' => $totalCustomers,
            'with_bookings' => $withBookings,
            'new_month' => $newThisMonth,
            'verified' => $verifiedCount,
        ];

        return view('livewire.admin.customers-page', [
            'customers' => $customers,
            'stats' => $stats,
        ])
            ->layout('layouts.dashboard')
            ->title('Daftar Customer | Dapur Nabilah');
    }
}
