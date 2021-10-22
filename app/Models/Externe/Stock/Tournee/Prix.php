<?php

namespace App\Models\Externe\Stock\Tournee;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    protected $guarded = [];
    protected $table = 'prix_tournees_externes';
    const RULES = [
        'prix_vente' => 'required|numeric',
    ];

    public function tournee()
    {
        return $this->belongsTo(Tournee::class);
    }
}
