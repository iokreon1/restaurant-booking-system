<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class StaffPage extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
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
        if ($this->editingId) {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            ];
            if ($this->password !== '') {
                $rules['password'] = ['required', 'string', 'confirmed', Password::defaults()];
            }
            $validated = $this->validate($rules);

            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            if ($this->password !== '') {
                $payload['password'] = Hash::make($validated['password']);
            }

            User::query()->whereKey($this->editingId)->where('role', User::ROLE_ADMIN)->update($payload);
            session()->flash('status', 'Staff diperbarui.');
        } else {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
            ]);

            User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_ADMIN,
            ]);
            session()->flash('status', 'Staff ditambahkan.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->addError('delete', 'Anda tidak dapat menghapus akun yang sedang aktif.');

            return;
        }

        $adminCount = User::query()->where('role', User::ROLE_ADMIN)->count();
        if ($adminCount <= 1) {
            $this->addError('delete', 'Minimal harus ada satu akun staff.');

            return;
        }

        $deleted = User::query()
            ->whereKey($id)
            ->where('role', User::ROLE_ADMIN)
            ->delete();

        if ($deleted === 0) {
            $this->addError('delete', 'Staff tidak ditemukan.');

            return;
        }

        session()->flash('status', 'Staff dihapus.');
    }

    public function render(): View
    {
        $staff = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->orderBy('name')
            ->paginate(10);

        $total = User::query()->where('role', User::ROLE_ADMIN)->count();
        $verified = User::query()->where('role', User::ROLE_ADMIN)->whereNotNull('email_verified_at')->count();

        $stats = [
            'total' => $total,
            'verified' => $verified,
        ];

        return view('livewire.admin.staff-page', [
            'staffMembers' => $staff,
            'stats' => $stats,
        ])
            ->layout('layouts.dashboard')
            ->title('Daftar Staff | Empon Pawon');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();
    }
}
