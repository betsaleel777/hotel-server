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
        'mere', 'departement', 'profession', 'pays', 'domicile', 'email', 'status',
    ];

    const RULES = [
        'nom' => 'required',
        'prenom' => 'required',
        'contact' => 'required|unique:clients,contact',
        'email' => 'nullable|email|unique:clients,email',
    ];

    const COMPLET = 'complet';
    const INCOMPLET = 'incomplet';

    public static function regles(int $id)
    {
        return [
            'nom' => 'required',
            'prenom' => 'required',
            'contact' => 'required|unique:clients,contact,' . $id,
            'email' => 'nullable|email|unique:clients,email,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function incomplet()
    {
        return empty($this->attributes['naissance']);
    }

    public function statut()
    {
        $this->incomplet() ? $this->attributes['status'] = self::INCOMPLET : $this->attributes['status'] = self::COMPLET;
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
