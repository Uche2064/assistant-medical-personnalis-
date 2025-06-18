<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Otp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'utilisateur_id',
        'code_otp',
        'verifier_a',
    ];

    protected function casts(): array
    {
        return [
            'verifier_a' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the OTP.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return $this->verifier_a < now();
    }


    /**
     * Generate a new OTP code.
     */
    public static function generateCode(int $length = 6): string
    {
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for a user.
     */
    public static function createForUser(User $user, string $type = 'login', int $expiryMinutes = 10): self
    {
        return self::create([
            'utilisateur_id' => $user->id,
            'code_otp' => self::generateCode(),
            'verifier_a' => now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Scope to get valid OTPs.
     */
    public function scopeValid($query)
    {
        return $query->where('verifier_a', '>', now());
    }

}
