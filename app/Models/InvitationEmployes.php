<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationEmployes extends Model
{
    
    protected $fillable = ['prospect_id', 'token', 'expire_at'];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
