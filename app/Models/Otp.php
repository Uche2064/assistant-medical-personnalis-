<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_otp',
        'verifier_a',
        'phone',
        'expires_a'
    ];

    protected function casts(): array
    {
        return [
            'verifier_a' => 'datetime',
            'expires_a' => 'datetime',
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
     * Checker si l'otp est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_a < Carbon::now();
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
    public static function updateOrCreateOtp($phone, $otp)
    {
        return self::updateOrCreate(
            ['phone' => $phone],
            ['code_otp' => $otp, 'expires_a' => Carbon::now()->addMinutes((int) env('OTP_EXPIRE_TIME', 5)), 'verifier_a' => null]
        );
    }

    /**
     * Scope to get valid OTPs.
     */
    public function scopeValid($query)
    {
        return $query->where('verifier_a', '>', Carbon::now());
    }

}
