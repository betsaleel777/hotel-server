<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employe extends Model
{
    use SoftDeletes;
    protected $fillable = ['nom', 'code', 'telephone', 'poste', 'prenom', 'email', 'adresse'];

    const RULES = [
        'nom' => 'required|unique:employes,nom',
        'prenom' => 'required',
        'poste' => 'required',
        'email' => 'nullable|email|unique:employes,email',
        'telephone' => 'required|unique:employes,telephone',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:employes,nom,' . $id,
            'prenom' => 'required',
            'poste' => 'required',
            'email' => 'nullable|email|unique:employes,email,' . $id,
            'telephone' => 'required|unique:employes,telephone,' . $id,
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }
}
