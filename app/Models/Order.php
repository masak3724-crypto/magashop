<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Ожидает обработки',
        'processing' => 'В обработке',
        'shipped' => 'Отправлен',
        'completed' => 'Завершен',
        'cancelled' => 'Отменен',
    ];

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'address',
        'postal_code', 'city', 'status', 'paid', 'payment_method',
    ];

    protected function casts(): array
    {
        return ['paid' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function totalCost(): float
    {
        return $this->items->sum(fn (OrderItem $item) => $item->cost());
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function paymentLabel(): string
    {
        if ($this->isCancelled()) {
            return 'Отменена';
        }

        return $this->paid ? 'Оплачен' : 'Не оплачен';
    }

    public function paymentBadgeClass(): string
    {
        if ($this->isCancelled()) {
            return 'payment-badge payment-cancelled';
        }

        return $this->paid ? 'bg-success' : 'bg-secondary';
    }

    public function countsTowardRevenue(): bool
    {
        return $this->paid && ! $this->isCancelled();
    }

    public function scopeRevenueEligible($query)
    {
        return $query->where('paid', true)->where('status', '!=', 'cancelled');
    }

    public function canBeCancelledByUser(): bool
    {
        return $this->status === 'processing';
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->status === 'cancelled') {
                $order->paid = false;
            }
        });
    }
}
