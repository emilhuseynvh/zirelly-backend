<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'contact_id',
        'status',
        'channel',
        'subtotal',
        'discount_amount',
        'total',
        'delivery_fee',
        'promocode_id',
        'promocode_code',
        'address',
        'note',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function changeStatus(OrderStatus $status, ?CrmUser $by = null, string $source = 'system'): void
    {
        if ($this->status === $status) {
            return;
        }

        $from = $this->status;

        $this->update([
            'status' => $status,
            'paid_at' => $status === OrderStatus::Paid ? ($this->paid_at ?? now()) : $this->paid_at,
        ]);

        $this->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $status->value,
            'crm_user_id' => $by?->id,
            'source' => $source,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }
}
