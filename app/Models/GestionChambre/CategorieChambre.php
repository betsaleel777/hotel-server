<?php

namespace App\Models\GestionChambre;

use Illuminate\Database\Eloquent\Model;

class CategorieChambre extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'categories_chambres';

    const RULES = [
        'nom' => 'required|unique:categories_chambres,nom',
    ];

    public function chambres()
    {
        return $this->hasMany(Chambre::class, 'categorie');
    }

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:categories_chambres,nom,' . $id,
        ];
    }
}
