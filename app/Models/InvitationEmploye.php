<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvitationEmploye extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entreprise_id',
        'token',
        'expire_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
    ];

    /**
     * Get the entreprise that owns the invitation.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Check if invitation is expired.
     */
    public function isExpired()
    {
        return $this->expire_at < now();
    }

    /**
     * Check if invitation is valid.
     */
    public function isValid()
    {
        return !$this->isExpired();
    }

    /**
     * Generate a unique token.
     */
    public static function generateToken()
    {
        do {
            $token = 'INV' . strtoupper(substr(md5(uniqid()), 0, 10));
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Create a new invitation with default expiration (7 days).
     */
    public static function createInvitation($entrepriseId, $days = 7)
    {
        return self::create([
            'entreprise_id' => $entrepriseId,
            'token' => self::generateToken(),
            'expire_at' => now()->addDays($days),
        ]);
    }

    /**
     * Get the invitation URL.
     */
    public function getInvitationUrlAttribute()
    {
        return url('/invitation/' . $this->token);
    }
} 