<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class OrdresReparation extends Model
{

    protected $fillable = ['code', 'entree', 'sortie', 'reparation_id', 'provider_id', 'fermeture', 'montant', 'devis', 'description', 'status'];
    protected $dates = ['entree', 'sortie'];

    const RULES = [
        'entree' => 'required',
        'sortie' => 'required',
        'provider_id' => 'required',
        'description' => 'required',
        'jour' => 'required',
        'montant' => 'required',
    ];
    const EDIT_RULES = [
        'entree' => 'required',
        'sortie' => 'required',
        'description' => 'required',
        'montant' => 'required',
    ];

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function reparation()
    {
        return $this->belongsTo(Reparation::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
