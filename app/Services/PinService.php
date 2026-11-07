<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class PinService
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    /**
     * Verify a user's PIN with rate-limiting and lockout.
     *
     * @throws \RuntimeException If locked out or PIN is invalid.
     */
    public function verify(User $user, string $pin): bool
    {
        // Check if locked out
        if ($this->isLockedOut($user)) {
            throw new \RuntimeException(
                'Too many PIN attempts. Please try again in ' . $this->lockoutRemainingMinutes($user) . ' minutes.'
            );
        }

        $verified = Hash::check($pin, $user->pin);

        // Record attempt
        $this->recordAttempt($user, $verified, request()->ip());

        if (!$verified) {
            $remaining = self::MAX_ATTEMPTS - $this->recentFailedAttempts($user);
            if ($remaining <= 0) {
                throw new \RuntimeException(
                    'Account locked due to too many failed PIN attempts. Try again in ' . self::LOCKOUT_MINUTES . ' minutes.'
                );
            }
            throw new \RuntimeException('Invalid PIN. ' . $remaining . ' attempts remaining.');
        }

        // Clear failed attempts on success
        $this->clearAttempts($user);
        return true;
    }

    /**
     * Set or update a user's PIN.
     *
     * @throws \InvalidArgumentException If PIN format is invalid.
     */
    public function setPin(User $user, string $pin): void
    {
        if (strlen($pin) !== 5 || !ctype_digit($pin)) {
            throw new \InvalidArgumentException('PIN must be exactly 5 digits.');
        }
        $user->update(['pin' => Hash::make($pin)]);
    }

    /**
     * Check if the user is currently locked out from PIN attempts.
     */
    public function isLockedOut(User $user): bool
    {
        return Cache::has('pin_locked_' . $user->id);
    }

    /**
     * Get remaining lockout time in minutes.
     */
    public function lockoutRemainingMinutes(User $user): int
    {
        $ttl = Cache::get('pin_locked_until_' . $user->id);
        if (!$ttl) {
            return 0;
        }
        return max(0, (int) ceil(now()->diffInMinutes($ttl, false)));
    }

    /**
     * Get the count of recent failed PIN attempts.
     */
    private function recentFailedAttempts(User $user): int
    {
        return Cache::get('pin_attempts_' . $user->id, 0);
    }

    /**
     * Record a PIN attempt in the database and update the cache counters.
     */
    private function recordAttempt(User $user, bool $success, ?string $ip = null): void
    {
        // Store in DB for audit trail
        DB::table('pin_attempts')->insert([
            'user_id' => $user->id,
            'ip_address' => $ip ?? 'unknown',
            'success' => $success,
            'created_at' => now(),
        ]);

        if (!$success) {
            $attempts = Cache::increment('pin_attempts_' . $user->id);
            if ($attempts === false) {
                Cache::put('pin_attempts_' . $user->id, 1, now()->addMinutes(self::LOCKOUT_MINUTES));
                $attempts = 1;
            }

            if ($attempts >= self::MAX_ATTEMPTS) {
                $lockUntil = now()->addMinutes(self::LOCKOUT_MINUTES);
                Cache::put('pin_locked_' . $user->id, true, $lockUntil);
                Cache::put('pin_locked_until_' . $user->id, $lockUntil, $lockUntil);
            }
        }
    }

    /**
     * Clear all failed attempt counters and lockout flags for a user.
     */
    private function clearAttempts(User $user): void
    {
        Cache::forget('pin_attempts_' . $user->id);
        Cache::forget('pin_locked_' . $user->id);
        Cache::forget('pin_locked_until_' . $user->id);
    }
}
