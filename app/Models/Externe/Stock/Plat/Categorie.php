<?php

namespace App\Models\Externe\Stock\Plat;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    protected $guarded = [];
    protected $cascadeDeletes = ['plats'];
    protected $table = 'categories_plats_externes';

    const RULES = [
        'nom' => 'required|unique:categories_plats_externes,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_plats_externes,nom,' . $id,
        ];
    }

    public function plats()
    {
        return $this->hasMany(Plat::class);
    }
}
