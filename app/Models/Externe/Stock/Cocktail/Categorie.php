<?php

namespace App\Models\Externe\Stock\Cocktail;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    protected $guarded = [];
    protected $cascadeDeletes = ['cocktails'];
    protected $table = 'categories_cocktails_externes';

    const RULES = [
        'nom' => 'required|unique:categories_cocktails_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_cocktails_externes,nom,' . $id,
        ];
    }

    public function cocktails()
    {
        return $this->hasMany(Cocktail::class);
    }
}
