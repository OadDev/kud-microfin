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
        'loan_type',
        'order_id',
        'approved_by',
        'approved_at',
        'reject_reason',
        'foreclosed_at',
        'foreclosure_amount',
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
            'approved_at' => 'datetime',
            'foreclosed_at' => 'datetime',
            'foreclosure_amount' => 'decimal:2',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
     * Only ever moves a loan between active/overdue/closed -- pending,
     * rejected and foreclosed are lifecycle stages this never touches.
     */
    public function refreshStatus(): void
    {
        if (! in_array($this->status, ['active', 'overdue', 'closed'], true)) {
            return;
        }

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

    /**
     * What the customer would need to pay right now to close the loan
     * immediately instead of continuing the scheduled EMIs: the remaining
     * outstanding balance plus a foreclosure interest charge (Admin-set
     * percentage of that outstanding balance, see PaymentSetting).
     */
    public function foreclosureBreakdown(): array
    {
        $outstanding = $this->outstanding();
        $rate = (float) PaymentSetting::current()->foreclosure_interest_rate;
        $interest = round($outstanding * $rate / 100, 2);

        return [
            'outstanding' => $outstanding,
            'interest_rate' => $rate,
            'interest_amount' => $interest,
            'total' => round($outstanding + $interest, 2),
        ];
    }

    public function foreclosureQuote(): float
    {
        return $this->foreclosureBreakdown()['total'];
    }

    /**
     * Settle all remaining EMIs at once and close the loan early.
     */
    public function foreclose(): void
    {
        $amount = $this->foreclosureQuote();

        $this->emis()->where('status', '!=', 'paid')->get()->each(function (Emi $emi) {
            $emi->update(['status' => 'paid', 'payment_date' => now()]);
        });

        $this->update([
            'status' => 'foreclosed',
            'foreclosed_at' => now(),
            'foreclosure_amount' => $amount,
        ]);
    }
}
