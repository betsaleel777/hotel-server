<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;
    protected $fillable = ['nom', 'code', 'telephone', 'prenom', 'email', 'adresse', 'categorie_id'];

    const RULES = [
        'nom' => 'required|unique:providers,nom',
        'prenom' => 'required',
        'categorie_id' => 'required',
        'email' => 'nullable|email|unique:providers,email',
        'telephone' => 'required|unique:providers,telephone',
    ];

    public static function regle(int $id)
    {
        return [
            'nom' => 'required|unique:providers,nom,' . $id,
            'prenom' => 'required',
            'categorie_id' => 'required',
            'email' => 'nullable|email|unique:providers,email,' . $id,
            'telephone' => 'required|unique:providers,telephone,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
}
