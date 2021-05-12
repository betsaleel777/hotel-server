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
}
