<?php

namespace App\Models\Stock;

use App\Models\Restaurant\Plat;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'nom', 'image', 'mode', 'type', 'mesure', 'description', 'etagere', 'categorie', 'prix_vente', 'pour_plat',
    ];

    const RULES = [
        'nom' => 'required|unique:produits,nom',
        'image' => 'nullable|max:10240',
        'type' => 'required',
        'mode' => 'required',
        'categorie' => 'required',
        'mesure' => 'nullable|max:20|required_if:mode,==,poids',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:produits,nom,' . $id,
            'image' => 'nullable|max:10240',
            'type' => 'required',
            'mode' => 'required',
            'mesure' => 'nullable|max:20|required_if:mode,==,poids',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function scopeCuisinable($query)
    {
        return $query->where('pour_plat', true);
    }

    public function scopeBuvable($query)
    {
        return $query->where('pour_plat', false);
    }

    public function achats()
    {
        return $this->hasMany(Achat::class, 'ingredient');
    }

    public function categorieLinked()
    {
        return $this->belongsTo(Categorie::class, 'categorie');
    }

    public function prixList()
    {
        return $this->hasMany(Prix::class, 'produit');
    }

    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'ingredients', 'produit', 'plat')->withPivot('commentaire', 'quantite')->withTimestamps();
    }
}
