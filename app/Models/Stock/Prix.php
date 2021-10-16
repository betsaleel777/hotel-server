<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'prix_produits';

    const RULES = [
        'montant' => 'required|numeric',
    ];

    public function produitLinked()
    {
        return $this->belongsTo(Produit::class, 'produit');
    }
}
