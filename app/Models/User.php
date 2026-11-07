<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'pin',
        'role',
        'avatar',
        'status',
        'two_factor_secret',
        'two_factor_enabled',
        'account_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin',
        'two_factor_secret',
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
            'two_factor_enabled' => 'boolean',
            'status' => 'string',
            'role' => 'string',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->account_id)) {
                $user->account_id = static::generateAccountId();
            }
        });
    }

    /**
     * Generate a unique account ID.
     */
    public static function generateAccountId(): string
    {
        do {
            $accountId = 'BRK' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (static::where('account_id', $accountId)->exists());

        return $accountId;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin';
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an agent.
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function sentTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class, 'user_id', 'sender_wallet_id');
    }

    public function receivedTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class, 'user_id', 'receiver_wallet_id');
    }

    public function adminDeposits(): HasMany
    {
        return $this->hasMany(AdminDeposit::class, 'admin_id');
    }

    public function apiClients(): HasMany
    {
        return $this->hasMany(ApiClient::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function pinAttempts(): HasMany
    {
        return $this->hasMany(PinAttempt::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }
}
