<?php

namespace App\Models\Maintenance;

use App\Models\GestionChambre\Chambre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reparation extends Model
{
    use SoftDeletes;
    protected $fillable = ['categorie_id', 'chambre_id', 'code', 'nom', 'status'];

    const RULES = [
        'categorie_id' => 'required',
        'chambre_id' => 'required',
        'jour' => 'required',
        'nom' => 'required|unique:reparations,nom',
    ];

    public static function regle(int $id)
    {
        return [
            'categorie_id' => 'required',
            'jour' => 'required',
            'nom' => 'required|unique:reparations,nom,' . $id,
        ];
    }

    const EN_COURS = 'current';
    const TERMINER = 'complete';
    const INACHEVER = 'incomplete';

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function scopeIncompleted($query)
    {
        return $query->where('status', self::INACHEVER);
    }

    public function chambre()
    {
        return $this->BelongsTo(Chambre::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function ordres()
    {
        return $this->hasMany(OrdresReparation::class);
    }
}
