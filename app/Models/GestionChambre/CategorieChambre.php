<?php

namespace App\Models\GestionChambre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieChambre extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nom', 'description',
    ];

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
