<?php

namespace App\Models;

use App\Support\ProductImageResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public ?string $resolved_image_url = null;

    protected $fillable = [
        'category_id', 'name', 'slug', 'price', 'description', 'image', 'available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'available' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function hasWhiteMatteBackground(): bool
    {
        if (! $this->image || str_starts_with($this->image, 'http')) {
            return false;
        }

        return in_array(
            strtolower(pathinfo($this->image, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg'],
            true
        );
    }

    public function hasImageFile(): bool
    {
        if (! $this->image || str_starts_with($this->image, 'http')) {
            return false;
        }

        return ! str_contains($this->imageUrl(), 'clothing-placeholder.svg');
    }

    public function imageUrl(): string
    {
        return ProductImageResolver::url($this);
    }

}
