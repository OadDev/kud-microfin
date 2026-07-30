<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'type',
        'generated_at',
        'signed_file_path',
        'signed_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'signed_at' => 'date',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
