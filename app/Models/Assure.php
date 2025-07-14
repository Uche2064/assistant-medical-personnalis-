<?php

namespace App\Models;

use App\Enums\LienEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'client_id', 'assure_principal_id',
        'contrat_id', 'lien_parente', 'est_principal'
    ];

    protected $casts = ['est_principal' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function principal()
    {
        return $this->belongsTo(Assure::class, 'assure_principal_id');
    }

    public function beneficiaires()
    {
        return $this->hasMany(Assure::class, 'assure_principal_id');
    }
}
