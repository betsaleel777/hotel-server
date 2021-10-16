<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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
