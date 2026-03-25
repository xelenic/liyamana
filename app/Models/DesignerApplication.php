<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignerApplication extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'experience',
        'certifications',
        'agreement_accepted',
        'identity_card_number',
        'identity_card_path',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'bank_name',
        'account_holder_name',
        'account_number',
        'routing_number',
        'swift_code',
        'status',
        'reviewed_at',
        'reviewed_by',
        'admin_notes',
    ];

    protected $casts = [
        'agreement_accepted' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
