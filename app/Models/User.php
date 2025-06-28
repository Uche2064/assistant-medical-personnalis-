<?php

namespace App\Models;

use App\Enums\SexeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = "users";
    protected $fillable = [
        'nom',
        'prenoms',
        'username',
        'email',
        'contact',
        'adresse',
        'raison_sociale',
        'sexe',
        'date_naissance',
        'est_actif',
        'password',
        'photo',
        'must_change_password',
        'email_verified_at',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mot_de_passe' => 'hashed',
            'date_naissance' => 'date',
            'adresse' => 'array',
            'sexe' => SexeEnum::class,
            'est_actif' => 'boolean',
            'must_change_password' => 'boolean'
        ];
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function otps()
    {
        return $this->hasMany(Otp::class);
    }

    static function genererMotDePasse($longueur = 8)
    {
        $lettres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chiffres = '0123456789';
        $caracteresSpeciaux = '!@#$&';

        $motDePasse = $caracteresSpeciaux[rand(0, strlen($caracteresSpeciaux) - 1)];

        $tousCaracteres = $lettres . $chiffres . $caracteresSpeciaux;

        for ($i = 1; $i < $longueur; $i++) {
            $motDePasse .= $tousCaracteres[rand(0, strlen($tousCaracteres) - 1)];
        }

        return str_shuffle($motDePasse);
    }



    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    public function isActive(): bool
    {
        return $this->est_actif;
    }


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

    public function gestionnaire()
    {
        return $this->hasOne(Gestionnaire::class, 'user_id');
    }

    public function prestataire()
    {
        return $this->hasOne(Prestataire::class, 'user_id');
    }
}
