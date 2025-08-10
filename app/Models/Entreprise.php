<?php

namespace App\Models;

use App\Enums\StatutClientEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'raison_sociale',
        'statut',
    ];

    protected $casts = [
        'statut' => StatutClientEnum::class,
    ];

    /**
     * Get the user that owns the entreprise.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assures (employees) for this entreprise.
     */
    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    /**
     * Get the invitation employes for this entreprise.
     */
    public function invitationEmployes()
    {
        return $this->hasMany(InvitationEmploye::class);
    }

    /**
     * Get the demandes adhesions for this entreprise.
     */
    public function demandesAdhesions()
    {
        return $this->user->demandes();
    }

    /**
     * Check if entreprise is active.
     */
    public function isActive()
    {
        return $this->statut === 'active';
    }

    /**
     * Check if entreprise is inactive.
     */
    public function isInactive()
    {
        return $this->statut === 'inactive';
    }

    /**
     * Generate a unique adhesion link.
     */
    public function generateAdhesionLink()
    {
        $token = 'ENT' . strtoupper(substr(md5(uniqid()), 0, 8));
        $this->lien_adhesion = $token;
        $this->save();
        
        return $token;
    }

    /**
     * Get the entreprise's name.
     */
    public function getNameAttribute()
    {
        return $this->raison_sociale;
    }

    /**
     * Get the number of active employees.
     */
    public function getActiveEmployeesCountAttribute()
    {
        return $this->assures()->where('statut', 'actif')->count();
    }
} 