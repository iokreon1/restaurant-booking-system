<?php

namespace App\Livewire\Microsite;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LandingPageMicrosite extends Component
{
    private const FEATURED_ITEMS_LIMIT = 3;

    /**
     * @return list<array{id: int, name: string, description: string, category: string, price_label: string, image: string, image_alt: string, is_sold_out: bool}>
     */
    #[Computed]
    public function featuredItems(): array
    {
        return $this->baseMenuItemsQuery()
            ->with(['category:id,name'])
            ->orderByRaw('case when status = ? then 0 else 1 end', [MenuItem::STATUS_AVAILABLE])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(self::FEATURED_ITEMS_LIMIT)
            ->get(['id', 'category_id', 'name', 'description', 'price', 'thumbnail_path', 'status'])
            ->map(function (MenuItem $menuItem): array {
                return [
                    'id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'description' => $menuItem->description,
                    'category' => $menuItem->category?->name ?? 'Menu Pilihan',
                    'price_label' => 'Rp '.number_format((float) $menuItem->price, 0, ',', '.'),
                    'image' => asset($menuItem->thumbnail_path),
                    'image_alt' => $menuItem->name,
                    'is_sold_out' => $menuItem->status === MenuItem::STATUS_SOLDOUT,
                ];
            })
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.microsite.landing-page-microsite')
            ->layout('layouts.microsite')
            ->title('Selamat Datang di Empon Pawon');
    }

    private function baseMenuItemsQuery(): Builder
    {
        return MenuItem::query()
            ->where('status', '!=', MenuItem::STATUS_INACTIVE);
    }
}
