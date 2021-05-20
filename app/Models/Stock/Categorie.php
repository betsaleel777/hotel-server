<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom',
    ];

    protected $table = 'categories_stock';

    const RULES = [
        'nom' => 'required|unique:categories_stock,nom',
    ];

    public function plats()
    {
        return $this->hasMany(Plat::class, 'categorie');
    }
}
