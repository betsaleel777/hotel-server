<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantite', 'commentaire', 'produit', 'plat',
    ];
}
