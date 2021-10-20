<?php

namespace App\Models\Externe\Stock\Plat;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    protected $table = 'prix_plats_externes';

    const RULES = [
        'montant' => 'required|numeric',
    ];

    public function plat()
    {
        return $this->belongsTo(Plat::class, 'plat');
    }
}
