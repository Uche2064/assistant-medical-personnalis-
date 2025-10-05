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
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
        'photo_url',
        'est_actif',
        'solde',
        'email_verifier_a',
        'mot_de_passe_a_changer',
        'personne_id',
        'lock_until',
        'permanently_blocked',
        'failed_attempts',
        'phase',
        'code_parrainage',
        'commercial_id',
        'compte_cree_par_commercial',
        'code_parrainage_commercial'
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
        'email_verifier_a' => 'datetime',
        'password' => 'hashed',
        'est_actif' => 'boolean',
        'mot_de_passe_a_changer' => 'boolean',
        'lock_until' => 'datetime',   // <-- important

    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function personne()
    {
        return $this->belongsTo(Personne::class);
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

    public function demandesAdhesions()
    {
        return $this->hasMany(DemandeAdhesion::class);
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
    // Demandes are now tied to Client; expose via client relation when present
    public function demandes()
    {
        return $this->hasManyThrough(
            DemandeAdhesion::class,
            Client::class,
            'user_id', // FK on clients
            'client_id', // FK on demandes_adhesions
            'id', // local key on users
            'id' // local key on clients
        );
    }

    /**
     * Get the client contrats associated with the user.
     */
    public function clientContrats()
    {
        return $this->hasManyThrough(
            ClientContrat::class,
            Client::class,
            'user_id', // FK on clients
            'client_id', // FK on clients_contrats
            'id',
            'id'
        );
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Alias de compatibilité: certaines parties du code chargent encore "entreprise".
     * Dans le nouveau schéma, l'entreprise est représentée par le modèle Client (client moral).
     * On expose donc une relation alias vers Client pour éviter les erreurs d'eager loading.
     */
    public function entreprise()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }


    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        if ($this->personnel) {
            return $this->personnel->nom . ' ' . $this->personnel->prenoms;
        }
        
        if ($this->client) {
            // If client is moral, you can adapt to show company name from related place if stored
            return $this->email;
        }
        
        return $this->email;
    }

    /**
     * Get the commercial who created this user account
     */
    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    /**
     * Get all clients created by this commercial
     */
    public function clientsParraines()
    {
        return $this->hasMany(User::class, 'commercial_id');
    }

    /**
     * Get the user's type
     */
    public function getUserTypeAttribute()
    {
        if ($this->personnel) return 'personnel';
        if ($this->client) return 'client';
        if ($this->assure) return 'assure';
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
