<?php

namespace App\Models\Externe\Stock\Cocktail;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    protected $table = 'prix_cocktails_externes';

    const RULES = [
        'montant' => 'required|numeric',
    ];

    public function cocktail()
    {
        return $this->belongsTo(Cocktail::class);
    }
}
