<?php

namespace App\Models\Externe\Caisse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use SoftDeletes;
    protected $fillable = ['nom', 'restaurant_id'];
    protected $table = 'tables_externes';

    const RULES = [
        'nom' => 'required|unique:tables_externes,nom',
    ];

    public static function regle(int $id)
    {
        return [
            'nom' => 'required|unique:tables_externes,nom,' . $id,
        ];
    }
}
