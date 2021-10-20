<?php

namespace App\Models\Externe\Stock\Tournee;

use App\Models\Externe\Stock\Article\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tournee extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'tournees_externes';
    const RULES = [
        'nom' => 'required|unique:tournees_externes,nom',
        'article_id' => 'required',
        'nombre' => 'required',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:tournees_externes,nom,' . $id,
            'article_id' => 'required',
            'nombre' => 'required',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
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
