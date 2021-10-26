<?php

namespace App\Models\Externe\Stock\Plat;

use App\Models\Externe\Stock\Article\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plat extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'plats_externes';

    const RULES = [
        'nom' => 'required|unique:plats_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:plats_externes,nom,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'ingredients_externes')->withPivot('quantite')->withTimestamps();
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
