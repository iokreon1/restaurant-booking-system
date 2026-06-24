<?php

namespace App\Models;

use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'thumbnail_path', 'status', 'sort_order'];

    const STATUS_AVAILABLE = 'available';

    const STATUS_SOLDOUT = 'soldout';

    const STATUS_INACTIVE = 'inactive';

    /**
     * @return BelongsTo<MenuCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }
}
