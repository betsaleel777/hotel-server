<?php

namespace App\Models\Externe\Stock\Depense;

use App\Models\Externe\Stock\Article\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depense extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'depenses_externes';

    const RULES = [
        'nom' => 'required|unique:depenses_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:depenses_externes,nom,' . $id,
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
        return $this->belongsToMany(Article::class, 'articles_depenses_externes')->withPivot('quantite', 'cout')->withTimestamps();
    }
}
