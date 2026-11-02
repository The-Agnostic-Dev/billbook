<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item) {
            $item->line_total = $item->calculateLineTotal();
        });

        static::created(fn (InvoiceItem $item) => $item->invoice->recalculate());
        static::deleted(fn (InvoiceItem $item) => $item->invoice->recalculate());
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function calculateLineTotal(): float
    {
        $gross = (float) $this->quantity * (float) $this->unit_price;

        return round($gross * (1 - (float) $this->discount_percent / 100), 2);
    }
}
