<?php

namespace App\Models\Externe\Stock\Article;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    protected $guarded = [];
    protected $cascadeDeletes = ['articles'];
    protected $table = 'categories_articles_externes';

    const RULES = [
        'nom' => 'required|unique:categories_articles_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_articles_externes,nom,' . $id,
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
