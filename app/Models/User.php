<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'surname', 'phone', 'birth_date', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $attributes = [
        'role' => UserRole::User->value,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'role' => UserRole::class,
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function basketItems(): HasMany
    {
        return $this->hasMany(BasketItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function recentViews(): HasMany
    {
        return $this->hasMany(RecentView::class);
    }

    public function recordProductView(Product $product, int $keep = 20): void
    {
        RecentView::query()->updateOrCreate(
            ['user_id' => $this->id, 'product_id' => $product->id],
            ['viewed_at' => now()],
        );

        $stale = $this->recentViews()
            ->orderByDesc('viewed_at')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($stale->isNotEmpty()) {
            RecentView::query()->whereKey($stale)->delete();
        }
    }
}