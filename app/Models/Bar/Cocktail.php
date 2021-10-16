<?php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Model;

class Cocktail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const RULES = [
        'nom' => 'nullable|unique:cocktails,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'nullable|unique:cocktails,nom,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function tournees()
    {
        return $this->belongsToMany(Tournee::class, 'cocktails_tournees', 'cocktail', 'tournee')->withPivot('quantite')->withTimestamps();
    }

    public function prixList()
    {
        return $this->hasMany(PrixCocktail::class, 'cocktail');
    }
}
