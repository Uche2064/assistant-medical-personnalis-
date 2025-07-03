<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'numero_police',
        'technicien_id',
        'photo_document',
        'prime',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'prime' => 'decimal:2',
        'photo_document' => 'json',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    
    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

}
