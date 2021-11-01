<?php

namespace App\Models\Externe\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mobile extends Model
{
    use SoftDeletes;
    protected $fillable = ['nom', 'restaurant_id'];
    protected $table = 'mobiles_externes';

    const RULES = [
        'nom' => 'required|unique:mobiles_externes,nom',
    ];

    public static function regle(int $id)
    {
        return [
            'nom' => 'required|unique:mobiles_externes,nom,' . $id,
        ];
    }
}
