<?php

namespace App\Models;

use App\Enums\TypePersonnelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'code_parainage',
        'gestionnaire_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gestionnaire()
    {
        return $this->belongsTo(Personnel::class, 'gestionnaire_id');
    }

    public function validations()
    {
        return $this->hasMany(DemandesAdhesions::class, 'valide_par_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'commercial_id');
    }
    public static function genererCodeParainage(): string
    {
        $code = strtoupper('PAR' . substr(uniqid(), -6));
        return $code;
    }
}
