<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Mail\OrderStatusMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        $this->notifyStatusChange($status, $source);
    }

    protected function notifyStatusChange(OrderStatus $status, string $source): void
    {
        // PaymentService onsuz da Paid keçidində qəbz emaili göndərir — dublikat olmasın
        if ($status === OrderStatus::Paid && $source === 'system') {
            return;
        }

        $email = $this->user?->email ?? $this->contact?->email;

        if (blank($email)) {
            Log::info("Order #{$this->id}: status email skipped — no user/contact email", [
                'status' => $status->value,
                'source' => $source,
            ]);

            return;
        }

        try {
            Mail::to($email)->send(new OrderStatusMail($this));

            Log::info("Order #{$this->id}: status email sent to {$email}", [
                'status' => $status->value,
                'source' => $source,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
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
