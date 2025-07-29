<?php

namespace App\Models;

use App\Enums\StatutClientEnum;
use App\Enums\TypeClientEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commercial_id',
        'type_client',
        'profession',
        'code_parainage',
        'statut',
        'nom',
        'prenoms',
        'sexe',
        'date_naissance',
    ];

    protected $casts = [
        'type_client' => TypeClientEnum::class,
        'statut' => StatutClientEnum::class,
    ];

    /**
     * Get the user that owns the client.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the commercial that manages this client.
     */
    public function commercial()
    {
        return $this->belongsTo(Personnel::class, 'commercial_id');
    }

    /**
     * Get the assures for this client.
     */
    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    /**
     * Get the demandes adhesions for this client.
     */
    public function demandesAdhesions()
    {
        return $this->user->demandes();
    }

    /**
     * Check if client is a prospect.
     */
    public function isProspect()
    {
        return $this->statut === StatutClientEnum::PROSPECT;
    }

    /**
     * Check if client is an active client.
     */
    public function isClient()
    {
        return $this->statut === StatutClientEnum::CLIENT;
    }

    /**
     * Check if client is an assure.
     */
    public function isAssure()
    {
        return $this->statut === StatutClientEnum::ASSURE;
    }

    /**
     * Check if client is physical.
     */
    public function isPhysique()
    {
        return $this->type_client === TypeClientEnum::PHYSIQUE;
    }

    /**
     * Check if client is moral (entreprise).
     */
    public function isMoral()
    {
        return $this->type_client === TypeClientEnum::MORAL;
    }

    /**
     * Promote client to next status.
     */
    public function promote()
    {
        if ($this->statut === StatutClientEnum::PROSPECT) {
            $this->statut = StatutClientEnum::CLIENT;
        } elseif ($this->statut === StatutClientEnum::CLIENT) {
            $this->statut = StatutClientEnum::ASSURE;
        }
        
        $this->save();
    }

    /**
     * Get the client's full name.
     */
    public function getFullNameAttribute()
    {
        if ($this->user) {
            return $this->user->full_name;
        }
        
        return 'Client #' . $this->id;
    }
}
