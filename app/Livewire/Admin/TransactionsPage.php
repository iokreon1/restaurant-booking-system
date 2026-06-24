<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsPage extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public string $paymentMethodFilter = 'all';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentMethodFilter(): void
    {
        $this->resetPage();
    }

    public function exportCsv(): void
    {
        session()->flash('status', 'Ekspor CSV/PDF akan segera hadir.');
    }

    /**
     * @return Builder<Transaction>
     */
    protected function filteredQuery()
    {
        $query = Transaction::query()
            ->with(['booking.table', 'booking.user']);

        if ($this->statusFilter === 'refunded') {
            $query->whereHas('booking', fn ($q) => $q->where('payment_status', Booking::PAYMENT_STATUS_REFUNDED));
        } elseif ($this->statusFilter === 'success') {
            $query->where('status', Transaction::STATUS_SUCCESS)
                ->whereHas('booking', fn ($q) => $q->where('payment_status', '!=', Booking::PAYMENT_STATUS_REFUNDED));
        } elseif ($this->statusFilter === 'failed') {
            $query->where('status', Transaction::STATUS_FAILED);
        } elseif ($this->statusFilter === 'pending') {
            $query->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_EXPIRED]);
        }

        if ($this->paymentMethodFilter !== 'all') {
            $query->where(function ($q) {
                $q->where('payment_method', $this->paymentMethodFilter)
                    ->orWhere('payment_channel', $this->paymentMethodFilter);
            });
        }

        return $query->latest('id');
    }

    public function render(): View
    {
        $transactions = $this->filteredQuery()->paginate(15);

        $paymentMethodChoices = Transaction::query()
            ->whereNotNull('payment_method')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method');

        $stats = [
            'total' => Transaction::query()->count(),
            'today_success' => Transaction::query()
                ->where('status', Transaction::STATUS_SUCCESS)
                ->whereDate('created_at', today())
                ->count(),
            'today_amount' => (float) Transaction::query()
                ->where('status', Transaction::STATUS_SUCCESS)
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        return view('livewire.admin.transactions-page', [
            'transactions' => $transactions,
            'stats' => $stats,
            'paymentMethodChoices' => $paymentMethodChoices,
        ])
            ->layout('layouts.dashboard')
            ->title('Transaksi | Dapur Nabilah');
    }
}
