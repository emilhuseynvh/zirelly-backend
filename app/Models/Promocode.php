<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\OrderStatus;
use App\Enums\PromocodeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promocode extends Model
{
    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'code',
        'type',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => PromocodeType::class,
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Returns an error code when the promocode cannot be used by the user,
     * or null when it is valid.
     */
    public function validateFor(User $user): ?string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        $now = now();

        if ($now->lt($this->starts_at)) {
            return 'not_started';
        }

        if ($now->gt($this->ends_at)) {
            return 'expired';
        }

        return match ($this->type) {
            PromocodeType::FirstOrder => $user->orders()
                ->where('status', '!=', OrderStatus::Cancelled)
                ->exists() ? 'first_order_only' : null,
            PromocodeType::SingleUse => $user->orders()
                ->where('promocode_id', $this->id)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->exists() ? 'already_used' : null,
            PromocodeType::Unlimited => null,
        };
    }

    public function discountFor(float $subtotal): float
    {
        $discount = match ($this->discount_type) {
            DiscountType::Percent => $subtotal * (float) $this->discount_value / 100,
            DiscountType::Fixed => (float) $this->discount_value,
        };

        return round(min($discount, $subtotal), 2);
    }

    public static function errorMessage(string $code): string
    {
        return match ($code) {
            'inactive' => 'This promocode is no longer active.',
            'not_started' => 'This promocode is not active yet.',
            'expired' => 'This promocode has expired.',
            'first_order_only' => 'This promocode is only valid for your first order.',
            'already_used' => 'You have already used this promocode.',
            default => 'This promocode cannot be applied.',
        };
    }
}
