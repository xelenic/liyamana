<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'google_id',
        'avatar',
        'balance',
        'special_offers_modal_shown_at',
        'intro_tour_multi_page_seen_at',
        'intro_tour_explore_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'special_offers_modal_shown_at' => 'datetime',
            'intro_tour_multi_page_seen_at' => 'datetime',
            'intro_tour_explore_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the flip books for the user.
     */
    public function flipBooks(): HasMany
    {
        return $this->hasMany(FlipBook::class);
    }

    public function aiContentGenerations(): HasMany
    {
        return $this->hasMany(AiContentGeneration::class);
    }

    /**
     * rrweb session recordings (when session recording is enabled).
     */
    public function sessionRecordings(): HasMany
    {
        return $this->hasMany(SessionRecording::class);
    }

    public function heatmapClicks(): HasMany
    {
        return $this->hasMany(UserHeatmapClick::class);
    }

    /**
     * True when phone or address is missing (user panel should prompt for details).
     */
    public function needsContactDetails(): bool
    {
        $phone = trim((string) ($this->phone ?? ''));
        $address = trim((string) ($this->address ?? ''));

        return $phone === '' || $address === '';
    }

    /**
     * Get credit transactions for the user.
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class)->latest();
    }

    /**
     * Add credits to user balance.
     */
    public function addCredits(float $amount, string $type = 'topup', ?string $paymentMethod = null, ?string $reference = null, ?string $description = null): CreditTransaction
    {
        $this->increment('balance', $amount);
        $this->refresh();

        return CreditTransaction::create([
            'user_id' => $this->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $this->balance,
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'description' => $description ?? 'Credit top-up',
            'status' => 'completed',
        ]);
    }
}
