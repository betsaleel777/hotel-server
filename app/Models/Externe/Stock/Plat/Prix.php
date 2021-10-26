<?php

namespace App\Models\Externe\Stock\Plat;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    protected $guarded = [];
    protected $table = 'prix_plats_externes';
    const RULES = [
        'prix_vente' => 'required|numeric',
    ];

    public function plat()
    {
        return $this->belongsTo(Plat::class, 'plat');
    }
}
