<?php

namespace App\Models\Externe\Stock\Article;

use App\Models\Externe\Parametre\Restaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'articles_externes';

    const RULES = [
        'nom' => 'required|unique:articles_externes,nom',
        'image' => 'nullable|max:10240',
        'type' => 'required',
        'categorie_id' => 'required',
        'mesure' => 'nullable|max:20',
        'contenance' => 'nullable|max:10',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:articles_externes,nom,' . $id,
            'image' => 'nullable|max:10240',
            'type' => 'required',
            'categorie_id' => 'required',
            'mesure' => 'nullable|max:20',
            'contenance' => 'nullable|max:10',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function scopeTournable($query)
    {
        return $query->where('pour_tournee', true);
    }

    public function scopePreparable($query)
    {
        return $query->where('pour_plat', true);
    }

    public function prix()
    {
        return $this->hasMany(Prix::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
}
