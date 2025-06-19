<?php

namespace App\Models;

use App\Enums\SexeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = "users";
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
        'photo',
        'must_change_password'
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
            'must_change_password' => 'boolean'
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

    public function client()
    {
        return $this->hasOne(Client::class, 'user_id');
    }

     public function assure()
    {
        return $this->hasOne(Assure::class, 'user_id');
    }

    public function personnel()
    {
        return $this->hasOne(Personnel::class, 'user_id');
    }

    public function prestataire()
    {
        return $this->hasOne(Prestataire::class, 'user_id');
    }
}
