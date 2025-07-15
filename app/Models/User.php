<?php

namespace App\Models;

use App\Enums\RoleEnum;
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

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'raison_sociale',
        'contact',
        'adresse',
        'sexe',
        'date_naissance',
        'password',
        'photo_url',
        'est_actif',
        'email_verified_at',
        'mot_de_passe_a_changer'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_naissance' => 'date',
            'adresse' => 'string',
            'sexe' => SexeEnum::class,
            'est_actif' => 'boolean',
            'mot_de_passe_a_changer' => 'boolean'
        ];
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->roles->first()->name,
            'email' => $this->email,
            'phone' => $this->contact,
            'username' => $this->username,
        ];
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


    public function personnel()
    {
        return $this->hasOne(Personnel::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function prospect()
    {
        return $this->hasOne(Prospect::class);
    }

    public function prestataire()
    {
        return $this->hasOne(Prestataire::class);
    }

    public function assure()
    {
        return $this->hasOne(Assure::class);
    }
    public function getRoles()
    {
        return $this->roles->pluck('name')->toArray();
    }
}
