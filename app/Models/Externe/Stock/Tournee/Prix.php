<?php

namespace App\Models\Externe\Stock\Tournee;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    protected $table = 'prix_tournees_externes';
    protected $guarded = [];
    const RULES = [
        'montant' => 'required|numeric',
    ];

    public function tournee()
    {
        return $this->belongsTo(Tournee::class);
    }
}
