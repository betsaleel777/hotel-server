<?php

namespace App\Models\Reception;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'nom', 'prenom', 'jeune_fille', 'naissance', 'contact', 'pere',
        'mere', 'departement', 'profession', 'pays', 'domicile', 'email',
    ];

    const RULES = [
        'nom' => 'required',
        'prenom' => 'required',
        'naissance' => 'required',
        'contact' => 'required|unique:clients,contact',
        'pere' => 'required',
        'mere' => 'required',
        'domicile' => 'required',
        'pays' => 'required',
        'email' => 'nullable|email|unique:clients,email',
        'profession' => 'required',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required',
            'prenom' => 'required',
            'naissance' => 'required',
            'contact' => 'required|unique:clients,contact,' . $id,
            'pere' => 'required',
            'mere' => 'required',
            'domicile' => 'required',
            'pays' => 'required',
            'email' => 'nullable|email|unique:clients,email,' . $id,
            'profession' => 'required',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function attributions()
    {
        return $this->hasMany(Attribution::class, 'client');
    }

    public function pieces()
    {
        return $this->hasMany(Piece::class, 'client');
    }

}
