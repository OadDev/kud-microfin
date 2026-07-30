<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'loan_id',
        'emi_id',
        'customer_id',
        'paid_amount',
        'method',
        'txn_reference',
        'screenshot_path',
        'remarks',
        'status',
        'reject_reason',
        'submitted_at',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function emi(): BelongsTo
    {
        return $this->belongsTo(Emi::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
