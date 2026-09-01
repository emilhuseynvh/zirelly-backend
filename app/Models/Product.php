<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasTranslations, HasUniqueSlug;

    protected $fillable = [
        'slug',
        'price',
        'discount',
        'discount_type',
        'is_active',
        'og_image_id',
    ];

    protected array $translatable = [
        'title',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'description',
        'pro_tip',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'discount_type' => DiscountType::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $product) {
            $product->features()->get()->each->delete();
            $product->howToUseSteps()->get()->each->delete();
        });
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Upload::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'og_image_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('position');
    }

    public function howToUseSteps(): HasMany
    {
        return $this->hasMany(ProductHowToUseStep::class)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->approved();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function finalPrice(): float
    {
        $price = (float) $this->price;

        if ($this->discount === null || $this->discount_type === null) {
            return $price;
        }

        $discounted = match ($this->discount_type) {
            DiscountType::Percent => $price - $price * (float) $this->discount / 100,
            DiscountType::Fixed => $price - (float) $this->discount,
        };

        return round(max($discounted, 0), 2);
    }
}