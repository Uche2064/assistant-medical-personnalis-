<?php

namespace App\Models;

use App\Enums\SexeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'contact',
        'adresse',
        'sexe',
        'date_naissance',
        'est_actif',
        'password',
        'role_id',
        'photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'date_naissance' => 'date',
            'adresse' => 'array',
            'sexe' => SexeEnum::class,
            'est_actif' => 'boolean',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the OTPs for the user.
     */
    public function otps()
    {
        return $this->hasMany(Otp::class);
    }

    /**
     * Get the conversations for the user.
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the messages sent by the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->est_actif;
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('est_actif', true);
    }
}
