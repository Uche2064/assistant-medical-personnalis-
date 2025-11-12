<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ClientTypeEnum;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_client',
    ];

    protected $casts = [
        'type_client' => ClientTypeEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    public function clientsContrats()
    {
        return $this->hasMany(ClientContrat::class);
    }

    public function isMoral() {
        return $this->type_client === ClientTypeEnum::MORAL;
    }

    public function isPhysique() {
        return $this->type_client === ClientTypeEnum::PHYSIQUE;
    }

    public function lienInvitations() {
        return $this->hasMany(LienInvitation::class);
    }
}


