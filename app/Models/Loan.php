<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shop_owner_id',
        'loan_account_no',
        'purpose',
        'principal',
        'interest',
        'processing_fee',
        'total_payable',
        'num_emis',
        'emi_amount',
        'frequency',
        'late_fee',
        'start_date',
        'first_due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'decimal:2',
            'interest' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'emi_amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'start_date' => 'date',
            'first_due_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shopOwner(): BelongsTo
    {
        return $this->belongsTo(ShopOwner::class);
    }

    public function emis(): HasMany
    {
        return $this->hasMany(Emi::class)->orderBy('emi_number');
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function amountPaid(): string
    {
        return number_format(
            $this->emis->where('status', 'paid')->sum('amount'),
            2, '.', ''
        );
    }

    public function outstanding(): float
    {
        return (float) $this->total_payable - (float) $this->amountPaid();
    }

    public function paidEmisCount(): int
    {
        return $this->emis->where('status', 'paid')->count();
    }

    public function nextDueEmi(): ?Emi
    {
        return $this->emis->first(fn (Emi $emi) => $emi->status !== 'paid');
    }

    /**
     * Recompute the loan's overall status from its EMIs. Called after any
     * payment approval/rejection so lists stay consistent without a cron.
     */
    public function refreshStatus(): void
    {
        $this->loadMissing('emis');

        if ($this->emis->every(fn (Emi $emi) => $emi->status === 'paid')) {
            $this->status = 'closed';
        } elseif ($this->emis->contains(fn (Emi $emi) => $emi->displayStatus() === 'Overdue')) {
            $this->status = 'overdue';
        } else {
            $this->status = 'active';
        }

        $this->save();
    }
}
