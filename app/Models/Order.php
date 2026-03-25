<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'template_name',
        'quantity',
        'total_amount',
        'payment_method',
        'status',
        'delivery_status',
        'checkout_data',
    ];

    public const DELIVERY_STATUSES = ['pending', 'hold', 'processing', 'sending', 'complete'];

    protected $casts = [
        'checkout_data' => 'array',
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
