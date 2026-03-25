<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockPurchase extends Model
{
    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_CHEQUE = 'cheque';

    public const PAYMENT_CREDIT = 'credit';

    protected $fillable = [
        'reference',
        'supplier_id',
        'supplier_name',
        'notes',
        'purchased_at',
        'payment_method',
        'subtotal',
        'discount',
        'deduction',
        'additional_charges',
        'total_cost',
        'user_id',
    ];

    protected $casts = [
        'purchased_at' => 'date',
        'subtotal' => 'decimal:4',
        'discount' => 'decimal:4',
        'deduction' => 'decimal:4',
        'additional_charges' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return array<string, string>
     */
    public static function paymentMethodOptions(): array
    {
        return [
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_CHEQUE => 'Cheque',
            self::PAYMENT_CREDIT => 'Credit',
        ];
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        if ($this->payment_method === null || $this->payment_method === '') {
            return null;
        }

        return self::paymentMethodOptions()[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Snapshot name on purchase, or linked supplier, for display and legacy rows.
     */
    public function getSupplierDisplayNameAttribute(): ?string
    {
        return $this->supplier?->name ?? $this->supplier_name;
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockPurchaseItem::class);
    }
}
