<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reparation extends Model
{
    use SoftDeletes;
    protected $fillable = ['categorie_id', 'chambre_id', 'code', 'nom', 'status'];

    const RULES = [
        'categorie_id' => 'required',
        'chambre_id' => 'required',
        'nom' => 'required|unique:reparation,nom',
    ];

    const EN_COURS = 'en cours';
    const TERMINER = 'terminer';
    const INACHEVER = 'inachever';

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function chambre()
    {
        return $this->BelongsTo(Chambre::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
}
