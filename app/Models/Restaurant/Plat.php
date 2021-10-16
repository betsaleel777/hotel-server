<?php

namespace App\Models\Restaurant;

use App\Models\Stock\Produit;
use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const RULES = [
        'image' => 'nullable|max:10240',
        'nom' => 'required|unique:plats,nom',
        'categorie' => 'required',
    ];

    public static function regles($id)
    {
        return [
            'image' => 'nullable|max:10240',
            'nom' => 'required|unique:plats,nom,' . $id,
            'categorie' => 'required',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function categorieLinked()
    {
        return $this->belongsTo(Categorie::class, 'categorie');
    }

    public function prix()
    {
        return $this->hasMany(Prix::class, 'plat');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'ingredients', 'plat', 'produit')->withPivot('commentaire', 'quantite')->withTimestamps();
    }

}
