<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand',
        'name',
        'description',
        'price',
        'down_payment',
        'storage',
        'ram',
        'network_type',
        'stock_quantity',
        'image_path',
        'video_url',
        'video_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function inStock(): bool
    {
        return $this->stock_quantity === null || $this->stock_quantity > 0;
    }

    public function financeable(): bool
    {
        return $this->down_payment !== null && (float) $this->down_payment < (float) $this->price;
    }

    public function hasVideo(): bool
    {
        return (bool) ($this->video_path || $this->video_url);
    }

    /**
     * A representative "starting from" monthly EMI for display/filtering,
     * based on a fixed 6-installment reference plan (the actual number of
     * installments is chosen by the customer at checkout).
     */
    public function referenceMonthlyEmi(): ?float
    {
        if (! $this->financeable()) {
            return null;
        }

        $processingFee = (float) \App\Models\PaymentSetting::current()->product_emi_processing_fee;

        return \App\Services\EmiQuoteService::quote((float) $this->price, (float) $this->down_payment, $processingFee, 6)['installment'];
    }
}
