<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nom',
    ];
    protected $table = 'categories_plats';

    const RULES = [
        'nom' => 'required|unique:categories_plats,nom',
    ];

    public function plats()
    {
        return $this->hasMany(Plat::class, 'categorie');
    }

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_plats,nom,' . $id,
        ];
    }
}
