<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'payment_terms_days',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function displayName(): string
    {
        return $this->company ? "{$this->company} ({$this->name})" : $this->name;
    }

    /**
     * Amount still owed across sent invoices. Expects invoices loaded with payments_sum_amount.
     */
    public function outstanding(): float
    {
        return round($this->invoices
            ->filter(fn (Invoice $invoice) => $invoice->sent_at !== null)
            ->sum(fn (Invoice $invoice) => max(0, $invoice->balance)), 2);
    }
}
