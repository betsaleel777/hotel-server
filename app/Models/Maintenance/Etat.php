<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class Etat extends Model
{
    protected $fillable = ['fourniture_id', 'chambre_id', 'libelle'];

    const RULES = [
        'fourniture_id' => 'required',
        'chambre_id' => 'required',
    ];

}
