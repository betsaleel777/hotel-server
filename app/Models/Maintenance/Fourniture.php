<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fourniture extends Model
{
    use SoftDeletes;
    protected $fillable = ['nom', 'code', 'description'];

    const RULES = [
        'nom' => 'required|unique:fournitures,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:fournitures,nom,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }
}
