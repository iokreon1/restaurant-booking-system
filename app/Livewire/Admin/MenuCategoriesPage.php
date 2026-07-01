<?php

namespace App\Livewire\Admin;

use App\Models\MenuCategory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class MenuCategoriesPage extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public int $sort_order = 0;

    public string $status = MenuCategory::STATUS_ACTIVE;

    public string $thumbnail_path = '';

    public $thumbnailUpload = null;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'thumbnailUpload' => [
                Rule::requiredIf(fn () => $this->editingId === null || $this->thumbnail_path === ''),
                'nullable',
                'image',
                'max:2048',
            ],
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
        $category = MenuCategory::query()->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->sort_order = $category->sort_order;
        $this->status = $category->status;
        $this->thumbnail_path = $category->thumbnail_path ?? '';
        $this->thumbnailUpload = null;
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
        $previousPath = $this->editingId
            ? MenuCategory::query()->whereKey($this->editingId)->value('thumbnail_path')
            : null;

        $thumbnailPath = $this->resolveThumbnailPathAfterSave($previousPath);

        $payload = [
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'status' => $validated['status'],
            'thumbnail_path' => $thumbnailPath,
        ];

        if ($this->editingId) {
            MenuCategory::query()->whereKey($this->editingId)->update($payload);
            session()->flash('status', 'Kategori diperbarui.');
        } else {
            MenuCategory::query()->create($payload);
            session()->flash('status', 'Kategori ditambahkan.');
        }

        $this->thumbnailUpload = null;
        $this->closeModal();
    }

    public function removeThumbnail(): void
    {
        $this->thumbnailUpload = null;
        $this->thumbnail_path = '';
    }

    public function delete(int $id): void
    {
        $category = MenuCategory::query()->withCount('menuItems')->findOrFail($id);

        if ($category->menu_items_count > 0) {
            $this->addError('delete', 'Hapus atau pindahkan menu di kategori ini terlebih dahulu.');

            return;
        }

        try {
            $category->delete();
            session()->flash('status', 'Kategori dihapus.');
        } catch (QueryException) {
            $this->addError('delete', 'Tidak dapat menghapus kategori ini.');
        }
    }

    public function render(): View
    {
        $categories = MenuCategory::query()
            ->withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $categories->count(),
            'active' => $categories->where('status', MenuCategory::STATUS_ACTIVE)->count(),
            'menu_total' => $categories->sum('menu_items_count'),
        ];

        return view('livewire.admin.menu-categories-page', [
            'categories' => $categories,
            'stats' => $stats,
        ])
            ->layout('layouts.dashboard')
            ->title('Kategori Menu | Empon Pawon');
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->sort_order = 0;
        $this->status = MenuCategory::STATUS_ACTIVE;
        $this->thumbnail_path = '';
        $this->thumbnailUpload = null;
        $this->resetErrorBag();
    }

    private function resolveThumbnailPathAfterSave(?string $previousPath): string
    {
        if ($this->thumbnailUpload) {
            $stored = $this->thumbnailUpload->store('menu-categories/thumbnails', 'public');
            $this->deletePublicStorageFileIfOwned($previousPath);

            return 'storage/'.$stored;
        }

        if ($this->editingId) {
            return $this->thumbnail_path;
        }

        return '';
    }

    private function isOwnedPublicStoragePath(?string $path): bool
    {
        return $path !== null
            && $path !== ''
            && ! Str::startsWith($path, ['http://', 'https://'])
            && Str::startsWith($path, 'storage/');
    }

    private function deletePublicStorageFileIfOwned(?string $thumbnailPath): void
    {
        if (! $this->isOwnedPublicStoragePath($thumbnailPath)) {
            return;
        }

        $relative = Str::after($thumbnailPath, 'storage/');
        Storage::disk('public')->delete($relative);
    }
}
