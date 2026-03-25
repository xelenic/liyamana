<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockPurchaseItem extends Model
{
    protected $fillable = [
        'stock_purchase_id',
        'purchasable_type',
        'purchasable_id',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:4',
    ];

    public function stockPurchase(): BelongsTo
    {
        return $this->belongsTo(StockPurchase::class);
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}
