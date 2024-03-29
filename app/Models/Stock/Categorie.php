<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use SoftDeletes;

    protected $fillable = [
        'nom',
    ];

    protected $table = 'categories_stock';

    const RULES = [
        'nom' => 'required|unique:categories_stock,nom',
    ];

    public function produits()
    {
        return $this->hasMany(Produit::class, 'categorie');
    }

    public function plats()
    {
        return $this->hasMany(Plat::class, 'categorie');
    }

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_stock,nom,' . $id,
        ];
    }
}
