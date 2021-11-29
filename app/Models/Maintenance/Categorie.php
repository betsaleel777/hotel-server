<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes;
    protected $fillable = ['nom'];

    protected $table = 'categories_reparations';

    const RULES = ['nom' => 'required|unique:categories_reparations,nom'];

    public static function regles(int $id)
    {
        return ['nom' => 'required|unique:categories_reparations,nom,' . $id];
    }

    public function reparations()
    {
        return $this->hasMany(Reparation::class);
    }
}
