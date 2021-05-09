<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'nom', 'image', 'mode', 'type', 'seuil', 'mesure',
    ];
    protected $table = 'produits_restau';

    const RULES = [
        'nom' => 'required|unique:produits_restau,nom',
        'image' => 'nullable|max:10240',
        'type' => 'required',
        'mode' => 'required',
        'seuil' => 'required|numeric',
        'mesure' => 'nullable|required_if:mode,==,poids',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:produits_restau,nom,' . $id,
            'image' => 'nullable|max:10240',
            'type' => 'required',
            'mode' => 'required',
            'seuil' => 'required|numeric',
            'mesure' => 'nullable|required_if:mode,==,poids',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function achats()
    {
        return $this->hasMany(Achat::class, 'ingredient');
    }
}
