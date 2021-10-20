<?php

namespace App\Models\Externe\Stock\Article;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    protected $table = 'prix_articles_externes';
    protected $guarded = [];
    const RULES = [
        'montant' => 'required|numeric',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
