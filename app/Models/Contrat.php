<?php

namespace App\Models;

use App\Enums\TypeContratEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'technicien_id',
        'prime_standard',
        'type_contrat',
    ];

    protected $casts = [
        'prime_standard' => 'decimal:2',
        'type_contrat' => TypeContratEnum::class,
    ];


    public function clientContrats()
    {
        return $this->hasMany(ClientContrat::class);
    }

    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

    public function categories()
    {
        return $this->belongsToMany(CategoriesGaranties::class, 'contrat_categorie_garantie')
            ->withPivot('couverture')
            ->withTimestamps();
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_contrat')
            ->withPivot('numero_police', 'date_debut', 'date_fin')
            ->withTimestamps();
    }
    

    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandesAdhesions::class, 'demande_adhesion_id');
    }
    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    public static function generateNumeroPolice()
    {
        // Generate a unique policy number
        do {
            $numero = 'POL-' . Str::upper(Str::random(10));
        } while (self::where('numero_police', $numero)->exists());

        return $numero;
    }
}
