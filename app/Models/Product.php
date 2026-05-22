<?php

namespace App\Models;

use App\Support\MarketplaceCatalog;
use App\Support\ProductImageResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public ?string $resolved_image_url = null;

    /** @var array<string, mixed>|null */
    private ?array $marketplaceMetaCache = null;

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

    public function marketplaceMeta(): array
    {
        if ($this->marketplaceMetaCache === null) {
            $this->marketplaceMetaCache = MarketplaceCatalog::meta($this->name);
        }

        return $this->marketplaceMetaCache;
    }

    public function cardSource(): string
    {
        return $this->marketplaceMeta()['source'];
    }

    public function cardBrand(): string
    {
        return $this->marketplaceMeta()['brand'];
    }

    public function oldPrice(): ?float
    {
        $old = $this->marketplaceMeta()['old_price'];

        return $old !== null ? (float) $old : null;
    }

    public function discountPercent(): int
    {
        return (int) ($this->marketplaceMeta()['discount'] ?? 0);
    }

    public function rating(): float
    {
        return (float) ($this->marketplaceMeta()['rating'] ?? 4.5);
    }

    public function reviewsCount(): int
    {
        return (int) ($this->marketplaceMeta()['reviews'] ?? 0);
    }

    public function hasDiscount(): bool
    {
        return $this->discountPercent() > 0 && $this->oldPrice() !== null;
    }
}
