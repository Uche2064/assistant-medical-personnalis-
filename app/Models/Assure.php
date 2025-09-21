<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Assure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'lien_parente',
        'est_principal',
        'assure_principal_id'
    ];

    protected $casts = [
        'est_principal' => 'boolean',
        'lien_parente' => \App\Enums\LienParenteEnum::class,
    ];

    /**
     * Get the user that owns the assure.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entreprise that owns the assure.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function reponsesQuestions()
    {
        return $this->hasMany(ReponseQuestion::class, 'assure_id');
    }

    /**
     * Get the beneficiaries for this principal assure.
     */
    public function beneficiaires()
    {
        return $this->hasMany(Assure::class, 'assure_principal_id');
    }

    /**
     * Get the principal assure for this beneficiary.
     */
    public function assurePrincipal()
    {
        return $this->belongsTo(Assure::class, 'assure_principal_id');
    }

    /**
     * Get the demande adhesion for this assure via client.
     */
    /**
     * Get the demande adhesion for this principal assure
     */
    public function demandeAdhesion()
    {
        return $this->hasOneThrough(DemandeAdhesion::class, Client::class, 'id', 'id', 'client_id', 'client_id')
            ->where('est_principal', true);
    }

    /**
     * Get the demande adhesion for this beneficiary (via principal assure)
     */
    public function demandeAdhesionViaPrincipal()
    {
        return $this->assurePrincipal->demandeAdhesion();
    }
    /**
     * Get the principal assure for this beneficiary.
     */
    // Removed principal/beneficiary self-reference per new schema

    /**
     * Get the beneficiaries for this principal assure.
     */
    // Removed beneficiaires relation per new schema

    /**
     * Get the contrat for this assure.
     */
    // Contract linkage handled via client->clientsContrats

    // Responses now tied to DemandeAdhesion via ReponseQuestion


    /**
     * Get the sinistres for this assure.
     */
    public function sinistres()
    {
        return $this->hasMany(Sinistre::class);
    }

    /**
     * Check if assure is principal.
     */
    public function isPrincipal()
    {
        return $this->est_principal;
    }

    /**
     * Check if assure is a beneficiary.
     */
    public function isBeneficiaire()
    {
        return !$this->est_principal && $this->assure_principal_id !== null;
    }

    /**
     * Check if this assure has beneficiaries.
     */
    public function hasBeneficiaires()
    {
        return $this->beneficiaires()->exists();
    }

    /**
     * Get the count of beneficiaries.
     */
    public function getBeneficiairesCountAttribute()
    {
        return $this->beneficiaires()->count();
    }

    /**
     * Check if assure is active.
     */
    // Status fields not present in new schema

    /**
     * Check if assure is inactive.
     */
    // Status fields not present in new schema

    /**
     * Check if assure is suspended.
     */
    // Status fields not present in new schema

    /**
     * Get the assure's full name.
     */
    // No name attributes in schema; keep for potential user full_name
    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->full_name : 'Assuré #' . $this->id;
    }

    /**
     * Get the assure's type (principal or beneficiary).
     */
    public function getTypeAttribute()
    {
        return $this->est_principal ? 'Principal' : 'Bénéficiaire';
    }

    /**
     * Get the assure's source (client or entreprise).
     */
    // Source simplified
    public function getSourceAttribute()
    {
        return $this->client ? 'Client' : 'Inconnu';
    }

    public function hasContratActif(): bool
    {
        $contratAssocie = $this->getContratAssocie();
        Log::info('Contrat associé : ' . $contratAssocie->statut->value);

        if (!$contratAssocie) {
            return false; // Pas de contrat associé
        }

        // On suppose que 'statut' indique l'état, et 'actif' signifie contrat en cours
        return $contratAssocie->statut->value === 'actif' || $contratAssocie->statut === \App\Enums\StatutContratEnum::ACTIF->value;
    }


    public function getContratAssocie()
    {
        return ClientContrat::with('typeContrat')
            ->where('client_id', $this->client_id)
            ->where('statut', \App\Enums\StatutContratEnum::ACTIF)
            ->first();
    }
}
