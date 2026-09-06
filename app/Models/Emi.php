<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emi extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'emi_number',
        'due_date',
        'amount',
        'amount_paid',
        'status',
        'payment_date',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    /**
     * How much is still owed on this EMI -- 0 once fully paid off, whether
     * that happened in one payment or several partial ones.
     */
    public function remainingAmount(): float
    {
        return max(0, round((float) $this->amount - (float) $this->amount_paid, 2));
    }

    /**
     * Persisted status only distinguishes paid / under_verification / pending.
     * The user-facing status (Upcoming / Due Today / Overdue) is derived from
     * the due date so it's always correct without a daily cron job.
     */
    public function displayStatus(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'under_verification' => 'Under Verification',
            default => match (true) {
                $this->due_date->isToday() => 'Due Today',
                $this->due_date->isPast() => 'Overdue',
                default => 'Upcoming',
            },
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->displayStatus()) {
            'Paid' => 'success',
            'Upcoming', 'Due Today' => 'warning',
            'Overdue' => 'danger',
            'Under Verification' => 'info',
            default => 'secondary',
        };
    }
}
