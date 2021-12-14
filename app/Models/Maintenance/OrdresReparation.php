<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class OrdresReparation extends Model
{

    protected $fillable = ['entree', 'sortie', 'reparation_id', 'provider_id', 'fermeture', 'montant', 'devis', 'description'];
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

    public function reparation()
    {
        return $this->belongsTo(Reparation::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
