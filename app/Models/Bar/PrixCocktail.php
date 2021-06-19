<?php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Model;

class PrixCocktail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cocktail', 'montant',
    ];

    protected $table = 'prix_cocktails';

    const RULES = [
        'montant' => 'required',
    ];

    public function cocktailLinked()
    {
        return $this->belongsTo(Cocktail::class, 'cocktail');
    }
}
