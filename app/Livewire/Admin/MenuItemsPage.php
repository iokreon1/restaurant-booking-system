<?php

namespace App\Livewire\Admin;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MenuItemsPage extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $categoryId = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public int $form_category_id = 0;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public string $status = MenuItem::STATUS_AVAILABLE;

    public int $sort_order = 0;

    public string $thumbnail_path = '';

    /** @var mixed */
    public $thumbnailUpload = null;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'form_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,soldout,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'thumbnailUpload' => [
                Rule::requiredIf(fn () => $this->editingId === null || $this->thumbnail_path === ''),
                'nullable',
                'image',
                'max:2048',
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = MenuItem::query()->findOrFail($id);
        $this->editingId = $item->id;
        $this->form_category_id = $item->category_id;
        $this->name = $item->name;
        $this->description = $item->description;
        $this->price = (string) $item->price;
        $this->status = $item->status;
        $this->sort_order = $item->sort_order;
        $this->thumbnail_path = $item->thumbnail_path ?? '';
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
            ? MenuItem::query()->whereKey($this->editingId)->value('thumbnail_path')
            : null;

        $thumbnailPath = $this->resolveThumbnailPathAfterSave($previousPath);

        $payload = [
            'category_id' => $validated['form_category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'],
            'thumbnail_path' => $thumbnailPath,
        ];

        if ($this->editingId) {
            MenuItem::query()->whereKey($this->editingId)->update($payload);
            session()->flash('status', 'Menu diperbarui.');
        } else {
            MenuItem::query()->create($payload);
            session()->flash('status', 'Menu ditambahkan.');
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
        MenuItem::query()->whereKey($id)->delete();
        session()->flash('status', 'Menu dihapus.');
    }

    public function render(): View
    {
        $categories = MenuCategory::query()->orderBy('name')->get();

        $itemsQuery = MenuItem::query()->with('category');

        if ($this->search !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
            $itemsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($this->categoryId !== '' && ctype_digit($this->categoryId)) {
            $itemsQuery->where('category_id', (int) $this->categoryId);
        }

        if ($this->statusFilter !== '') {
            $itemsQuery->where('status', $this->statusFilter);
        }

        $items = $itemsQuery
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        $stats = [
            'total' => MenuItem::query()->count(),
            'available' => MenuItem::query()->where('status', MenuItem::STATUS_AVAILABLE)->count(),
            'soldout' => MenuItem::query()->where('status', MenuItem::STATUS_SOLDOUT)->count(),
        ];

        return view('livewire.admin.menu-items-page', [
            'categories' => $categories,
            'items' => $items,
            'stats' => $stats,
        ])
            ->layout('layouts.dashboard')
            ->title('Menu Makanan | Dapur Nabilah');
    }

    private function resetForm(): void
    {
        $firstCategoryId = MenuCategory::query()->orderBy('name')->value('id');
        $this->form_category_id = $firstCategoryId ? (int) $firstCategoryId : 0;
        $this->name = '';
        $this->description = '';
        $this->price = '';
        $this->status = MenuItem::STATUS_AVAILABLE;
        $this->sort_order = 0;
        $this->thumbnail_path = '';
        $this->thumbnailUpload = null;
        $this->resetErrorBag();
    }

    private function resolveThumbnailPathAfterSave(?string $previousPath): string
    {
        if ($this->thumbnailUpload) {
            $stored = $this->thumbnailUpload->store('menu-items/thumbnails', 'public');
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
