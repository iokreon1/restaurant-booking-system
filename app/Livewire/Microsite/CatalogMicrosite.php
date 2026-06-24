<?php

namespace App\Livewire\Microsite;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CatalogMicrosite extends Component
{
    private const DEFAULT_VISIBLE_PRODUCTS = 10;

    public string $selectedCategory = 'all';

    public string $search = '';

    public int $visibleCount = self::DEFAULT_VISIBLE_PRODUCTS;

    /**
     * @var list<array{slug: string, label: string, name?: string}>
     */
    public array $categories = [];

    public function mount(): void
    {
        $this->categories = MenuCategory::query()
            ->where('status', MenuCategory::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name'])
            ->map(fn (MenuCategory $category): array => [
                'slug' => Str::slug($category->name),
                'label' => $category->name,
                'name' => $category->name,
            ])
            ->prepend(['slug' => 'all', 'label' => 'All'])
            ->values()
            ->all();
    }

    public function selectCategory(string $slug): void
    {
        $this->selectedCategory = $slug;
        $this->visibleCount = self::DEFAULT_VISIBLE_PRODUCTS;
    }

    public function updatedSearch(): void
    {
        $this->visibleCount = self::DEFAULT_VISIBLE_PRODUCTS;
    }

    public function loadMore(): void
    {
        $this->visibleCount += self::DEFAULT_VISIBLE_PRODUCTS;
    }

    /**
     * @return list<array{id: int, name: string, category: string, price_label: string, price_value: float, image: string, image_alt: string, rating: string, available: bool}>
     */
    #[Computed]
    public function filteredItems(): array
    {
        return $this->baseMenuItemQuery()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($this->visibleCount)
            ->get(['id', 'category_id', 'name', 'price', 'thumbnail_path', 'status'])
            ->map(function (MenuItem $item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => Str::slug($item->category?->name ?? ''),
                    'price_label' => 'Rp '.number_format((float) $item->price, 0, ',', '.'),
                    'price_value' => (float) $item->price,
                    'image' => asset($item->thumbnail_path),
                    'image_alt' => $item->name,
                    'rating' => '4.8',
                    'available' => $item->status === MenuItem::STATUS_AVAILABLE,
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function hasMoreItems(): bool
    {
        return $this->baseMenuItemQuery()->count() > count($this->filteredItems);
    }

    private function baseMenuItemQuery(): Builder
    {
        $searchKeyword = trim($this->search);
        $selectedCategoryName = collect($this->categories)
            ->firstWhere('slug', $this->selectedCategory)['name'] ?? null;

        return MenuItem::query()
            ->with(['category:id,name'])
            ->where('status', '!=', MenuItem::STATUS_INACTIVE)
            ->when($selectedCategoryName, function (Builder $query, string $categoryName): void {
                $query->whereHas('category', function (Builder $categoryQuery) use ($categoryName): void {
                    $categoryQuery->where('name', $categoryName);
                });
            })
            ->when($searchKeyword !== '', function (Builder $query) use ($searchKeyword): void {
                $query->where(function (Builder $searchQuery) use ($searchKeyword): void {
                    $searchQuery
                        ->where('name', 'like', "%{$searchKeyword}%")
                        ->orWhere('description', 'like', "%{$searchKeyword}%");
                });
            });
    }

    public function render()
    {
        return view('livewire.microsite.catalog-microsite')
            ->layout('layouts.microsite')
            ->title('Katalog Menu | The Organic Atelier');
    }
}
