<?php

namespace App\Models\Parametre;

use App\Models\Stock\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom', 'description',
    ];

    const RULES = [
        'nom' => 'required|max:150|unique:departements,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|max:150|unique:departements,nom,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function produit()
    {
        return $this->hasMany(Demande::class, 'demande');
    }

    public function userLinked()
    {
        return $this->belongsTo(User::class, 'user');
    }

}
