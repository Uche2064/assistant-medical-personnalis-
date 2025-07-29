<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $table = 'otp';

    protected $fillable = [
        'email',
        'otp',
        'expire_at',
        'verifier_a',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'verifier_a' => 'datetime',

    ];

    /**
     * Check if OTP is expired.
     */
    public function isExpired()
    {
        return $this->expire_at < now();
    }

    /**
     * Check if OTP is valid.
     */
    public function isValid()
    {
        return !$this->isExpired();
    }

    /**
     * Generate a new OTP.
     */
    public static function generateOtp($email, $minutes = 10)
    {
        // Delete existing OTPs for this email
        self::where('email', $email)->delete();

        // Generate new OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'email' => $email,
            'otp' => $otp,
            'expire_at' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Verify OTP.
     */
    public static function verifyOtp($email, $otp)
    {
        $otpRecord = self::where('email', $email)
                         ->where('otp', $otp)
                         ->where('expire_at', '>', now())
                         ->first();

        if ($otpRecord) {
            $otpRecord->delete(); // Delete after successful verification
            return true;
        }

        return false;
    }

    /**
     * Clean expired OTPs.
     */
    public static function cleanExpired()
    {
        return self::where('expire_at', '<', now())->delete();
    }
}
