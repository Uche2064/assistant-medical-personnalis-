<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CommercialParrainageCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'commercial_id',
        'code_parrainage',
        'date_debut',
        'date_expiration',
        'est_actif',
        'est_renouvele'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_expiration' => 'datetime',
        'est_actif' => 'boolean',
        'est_renouvele' => 'boolean'
    ];

    /**
     * Relation avec le commercial
     */
    public function commercial()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }

    /**
     * Scope pour les codes actifs
     */
    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour les codes expirés
     */
    public function scopeExpire($query)
    {
        return $query->where('date_expiration', '<', now());
    }

    /**
     * Scope pour les codes non expirés
     */
    public function scopeNonExpire($query)
    {
        return $query->where('date_expiration', '>', now());
    }

    /**
     * Vérifier si le code est expiré
     */
    public function isExpired()
    {
        return $this->date_expiration->isPast();
    }

    /**
     * Vérifier si le code peut être renouvelé
     */
    public function canBeRenewed()
    {
        return $this->isExpired() && !$this->est_renouvele;
    }

    /**
     * Obtenir le code actuel d'un commercial
     */
    public static function getCurrentCode($commercialId)
    {
        return static::where('commercial_id', $commercialId)
            ->where('est_actif', true)
            ->where('date_expiration', '>', now())
            ->first();
    }

    /**
     * Obtenir l'historique des codes d'un commercial
     */
    public static function getHistory($commercialId)
    {
        return static::where('commercial_id', $commercialId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Générer un nouveau code pour un commercial
     */
    public static function generateNewCode($commercialId)
    {
        // Désactiver l'ancien code s'il existe
        static::where('commercial_id', $commercialId)
            ->where('est_actif', true)
            ->update(['est_actif' => false]);

        // Générer un code unique
        $code = 'COM' . strtoupper(\Illuminate\Support\Str::random(6));
        
        // Vérifier l'unicité
        while (static::where('code_parrainage', $code)->exists()) {
            $code = 'COM' . strtoupper(\Illuminate\Support\Str::random(6));
        }

        // Créer le nouveau code
        $now = now();
        $expiration = $now->copy()->addYear(); // 1 an

        return static::create([
            'commercial_id' => $commercialId,
            'code_parrainage' => $code,
            'date_debut' => $now,
            'date_expiration' => $expiration,
            'est_actif' => true,
            'est_renouvele' => false
        ]);
    }
}