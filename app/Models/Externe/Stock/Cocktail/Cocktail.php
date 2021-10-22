<?php

namespace App\Models\Externe\Stock\Cocktail;

use App\Models\Externe\Stock\Tournee\Tournee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cocktail extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'cocktails_externes';

    const RULES = [
        'nom' => 'required|unique:cocktails_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:cocktails_externes,nom,' . $id,
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
        return $this->belongsToMany(Tournee::class, 'melanges_externes')->withPivot('quantite')->withTimestamps();
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function prix()
    {
        return $this->hasMany(Prix::class);
    }
}
