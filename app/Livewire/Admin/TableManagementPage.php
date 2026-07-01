<?php

namespace App\Livewire\Admin;

use App\Models\Table;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class TableManagementPage extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $table_number = '';

    public int $capacity = 2;

    public string $location_description = '';

    public string $status = Table::STATUS_AVAILABLE;

    /**
     * @return array<string, array<int, mixed|string>>
     */
    protected function rules(): array
    {
        return [
            'table_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tables', 'table_number')->ignore($this->editingId),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:99'],
            'location_description' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'in:available,booked,maintenance,inactive'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $table = Table::query()->findOrFail($id);
        $this->editingId = $table->id;
        $this->table_number = $table->table_number;
        $this->capacity = $table->capacity;
        $this->location_description = $table->location_description;
        $this->status = $table->status;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingId = null;
    }

    public function save(): void
    {
        $validated = $this->validate();
        $payload = [
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'location_description' => $validated['location_description'],
            'status' => $validated['status'],
        ];

        if ($this->editingId) {
            Table::query()->whereKey($this->editingId)->update($payload);
            session()->flash('status', 'Meja diperbarui.');
        } else {
            Table::query()->create($payload);
            session()->flash('status', 'Meja ditambahkan.');
        }

        $this->closeModal();
    }

    public function setAvailable(int $id): void
    {
        $table = Table::query()->findOrFail($id);
        $table->update(['status' => Table::STATUS_AVAILABLE]);
        session()->flash('status', 'Meja diatur ke status tersedia.');
    }

    public function delete(int $id): void
    {
        $table = Table::query()->withCount('bookings')->findOrFail($id);

        if ($table->bookings_count > 0) {
            $this->addError('delete', 'Hapus atau selesaikan booking yang memakai meja ini terlebih dahulu.');

            return;
        }

        try {
            $table->delete();
            session()->flash('status', 'Meja dihapus.');
            if ($this->editingId === $id) {
                $this->closeModal();
            }
        } catch (QueryException) {
            $this->addError('delete', 'Tidak dapat menghapus meja ini.');
        }
    }

    public function render(): View
    {
        $tables = Table::query()
            ->with(['latestBooking.user'])
            ->orderBy('table_number')
            ->get();

        $stats = [
            'total' => $tables->count(),
            'available' => $tables->where('status', Table::STATUS_AVAILABLE)->count(),
            'booked' => $tables->where('status', Table::STATUS_BOOKED)->count(),
            'maintenance' => $tables->where('status', Table::STATUS_MAINTENANCE)->count(),
            'inactive' => $tables->where('status', Table::STATUS_INACTIVE)->count(),
        ];

        return view('livewire.admin.table-management-page', [
            'tables' => $tables,
            'stats' => $stats,
            'tableStatusLabels' => [
                Table::STATUS_AVAILABLE => 'Tersedia',
                Table::STATUS_BOOKED => 'Terisi',
                Table::STATUS_MAINTENANCE => 'Maintenance',
                Table::STATUS_INACTIVE => 'Nonaktif',
            ],
        ])
            ->layout('layouts.dashboard')
            ->title('Manajemen Meja | Empon Pawon');
    }

    private function resetForm(): void
    {
        $this->table_number = '';
        $this->capacity = 2;
        $this->location_description = '';
        $this->status = Table::STATUS_AVAILABLE;
        $this->resetErrorBag();
    }
}
