<?php

namespace App\Models\Bar;

use App\Models\Stock\Produit;
use Illuminate\Database\Eloquent\Model;

class Tournee extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const RULES = [
        'titre' => 'nullable|unique:tournees,titre',
        'produit' => 'required',
        'nombre' => 'required',
        'contenance' => 'required',
    ];

    public static function regles(int $id)
    {
        return [
            'titre' => 'required|unique:tournees,titre,' . $id,
            'produit' => 'required',
            'nombre' => 'required',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function produitLinked()
    {
        return $this->belongsTo(Produit::class, 'produit');
    }

    public function prixList()
    {
        return $this->hasMany(Prix::class, 'tournee');
    }
}
