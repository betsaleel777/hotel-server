<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $table = 'approvisionements';

    const RULES = [
        'quantite' => 'required',
        'prix_achat' => 'required',
        'ingredient' => 'required',
    ];

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'ingredient');
    }
}
