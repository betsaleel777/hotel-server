<?php

namespace App\Models\Maintenance;

use App\Models\GestionChambre\Chambre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entretien extends Model
{
    use SoftDeletes;
    protected $fillable = ['employe_id', 'chambre_id', 'entree', 'sortie', 'description', 'code'];

    const RULES = [
        'employe_id' => 'required',
        'chambre_id' => 'required',
        'entree' => 'required',
        'sortie' => 'required',
    ];

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

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
