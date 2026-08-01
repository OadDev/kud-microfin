<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_code',
        'father_name',
        'alt_mobile',
        'dob',
        'gender',
        'address',
        'city',
        'state',
        'pin',
        'pan',
        'aadhaar',
        'photo_path',
        'aadhaar_doc_path',
        'pan_doc_path',
        'shop_owner_id',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shopOwner(): BelongsTo
    {
        return $this->belongsTo(ShopOwner::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    /**
     * A customer is created together with exactly one loan today; this
     * returns that loan (or the most recent, if that ever changes).
     */
    public function currentLoan(): ?Loan
    {
        return $this->relationLoaded('loans')
            ? $this->loans->sortByDesc('id')->first()
            : $this->loans()->latest('id')->first();
    }
}
