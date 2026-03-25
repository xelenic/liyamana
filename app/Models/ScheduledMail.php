<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledMail extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'template_name',
        'address_book_id',
        'recipient_snapshot',
        'send_at',
        'credit_amount',
        'checkout_data',
        'quantity',
        'status',
        'order_id',
        'error_message',
    ];

    protected $casts = [
        'send_at' => 'datetime',
        'credit_amount' => 'decimal:2',
        'recipient_snapshot' => 'array',
        'checkout_data' => 'array',
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

    public function addressBook(): BelongsTo
    {
        return $this->belongsTo(AddressBook::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
