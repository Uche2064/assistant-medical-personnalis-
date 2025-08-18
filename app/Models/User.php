<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'contact',
        'password',
        'adresse',
        'photo',
        'est_actif',
        'email_verified_at',
        'mot_de_passe_a_changer',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'est_actif' => 'boolean',
        'mot_de_passe_a_changer' => 'boolean',
    ];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the JWT custom claims for the user.
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->getUserTypeAttribute(),
            'email' => $this->email,
            'user_id' => $this->id,
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the personnel record associated with the user.
     */
    public function personnel()
    {
        return $this->hasOne(Personnel::class);
    }

    /**
     * Get the entreprise record associated with the user.
     */
    public function entreprise()
    {
        return $this->hasOne(Entreprise::class);
    }

    /**
     * Get the assure record associated with the user.
     */
    public function assure()
    {
        return $this->hasOne(Assure::class);
    }

    /**
     * Get the prestataire record associated with the user.
     */
    public function prestataire()
    {
        return $this->hasOne(Prestataire::class);
    }

    /**
     * Get the demandes d'adhésion associated with the user.
     */
    public function demandes()
    {
        return $this->hasMany(DemandeAdhesion::class);
    }

    /**
     * Get the client contrats associated with the user.
     */
    public function clientContrats()
    {
        return $this->hasMany(ClientContrat::class, 'user_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the conversations where the user is participant 1.
     */
    public function conversationsAsUser1()
    {
        return $this->hasMany(Conversation::class, 'user_id_1');
    }

    /**
     * Get the conversations where the user is participant 2.
     */
    public function conversationsAsUser2()
    {
        return $this->hasMany(Conversation::class, 'user_id_2');
    }

    /**
     * Get all conversations for the user.
     */
    public function conversations()
    {
        return $this->conversationsAsUser1()->union($this->conversationsAsUser2());
    }

    /**
     * Get the messages sent by the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        if ($this->personnel) {
            return $this->personnel->nom . ' ' . $this->personnel->prenoms;
        }
        
        if ($this->entreprise) {
            return $this->entreprise->raison_sociale;
        }
        
        return $this->email;
    }

    /**
     * Get the user's type
     */
    public function getUserTypeAttribute()
    {
        if ($this->personnel) return 'personnel';
        if ($this->client) return 'client';
        if ($this->entreprise) return 'entreprise';
        if ($this->assure) return 'assure';
        if ($this->prestataire) return 'prestataire';
        if($this->prestataire) return 'prestataire';
        
        
        return 'user';
    }

    /**
     * Generate a random password
     */
    public static function genererMotDePasse()
    {
        return Str::random(8);
    }
}
