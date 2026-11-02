<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'partial' => 'Part paid',
        'overdue' => 'Overdue',
        'paid' => 'Paid',
    ];

    private const PAID_SUM_SQL = '(select coalesce(sum(amount), 0) from payments where payments.invoice_id = invoices.id)';

    protected $fillable = [
        'client_id',
        'number',
        'issue_date',
        'due_date',
        'sent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_on');
    }

    public static function nextNumber(): string
    {
        $sequence = static::count() + 1;

        return config('billbook.invoice_prefix').str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Recompute the stored totals from the line items.
     */
    public function recalculate(): void
    {
        $items = $this->items()->get();

        $gross = round($items->sum(fn (InvoiceItem $item) => $item->quantity * $item->unit_price), 2);
        $subtotal = round($items->sum(fn (InvoiceItem $item) => (float) $item->line_total), 2);
        $vat = round($subtotal * config('billbook.vat_rate'), 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'discount_total' => round($gross - $subtotal, 2),
            'vat_total' => $vat,
            'total' => round($subtotal + $vat, 2),
        ])->saveQuietly();
    }

    /**
     * Create, update and remove line items to match the submitted rows.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncItems(array $rows): void
    {
        $keep = [];

        foreach (array_values($rows) as $position => $row) {
            $attributes = [
                'description' => $row['description'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'discount_percent' => $row['discount_percent'] ?? 0,
                'position' => $position,
            ];

            $item = empty($row['id']) ? null : $this->items()->find($row['id']);

            if ($item) {
                $item->update($attributes);
            } else {
                $item = $this->items()->create($attributes);
            }

            $keep[] = $item->id;
        }

        $this->items()->whereNotIn('id', $keep)->get()->each->delete();
    }

    protected function amountPaid(): Attribute
    {
        return Attribute::get(fn () => round((float) (array_key_exists('payments_sum_amount', $this->attributes)
            ? $this->attributes['payments_sum_amount']
            : $this->payments->sum('amount')), 2));
    }

    protected function balance(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->total - $this->amount_paid, 2));
    }

    public function isOverdue(): bool
    {
        return $this->sent_at !== null
            && $this->balance > 0
            && $this->due_date->lt(now());
    }

    public function status(): string
    {
        return match (true) {
            $this->sent_at === null => 'draft',
            $this->balance <= 0 => 'paid',
            $this->isOverdue() => 'overdue',
            $this->amount_paid > 0 => 'partial',
            default => 'sent',
        };
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status()];
    }

    public function scopeDraft(Builder $query): void
    {
        $query->whereNull('sent_at');
    }

    public function scopeSent(Builder $query): void
    {
        $query->whereNotNull('sent_at');
    }

    /** Sent invoices the client still has to pay. */
    public function scopeUnpaid(Builder $query): void
    {
        $query->sent()->whereDoesntHave('payments');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->sent()
            ->whereDate('due_date', '<', today())
            ->whereRaw('total > '.self::PAID_SUM_SQL);
    }

    public function scopePaid(Builder $query): void
    {
        $query->sent()->whereRaw('total <= '.self::PAID_SUM_SQL);
    }
}
