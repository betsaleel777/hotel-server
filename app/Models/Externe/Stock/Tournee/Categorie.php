<?php

namespace App\Models\Externe\Stock\Tournee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $cascadeDeletes = ['tournees'];
    protected $table = 'categories_tournees_externes';

    const RULES = [
        'nom' => 'required|unique:categories_tournees_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_tournees_externes,nom,' . $id,
        ];
    }

    public function tournees()
    {
        return $this->hasMany(Tournee::class);
    }
}
