<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class OrdresReparation extends Model
{

    protected $fillable = ['entree', 'sortie', 'reparation_id', 'provider_id', 'fermeture'];
    protected $dates = ['entree', 'sortie'];

    const RULES = [
        'entree' => 'required',
        'sortie' => 'required',
        'provider_id' => 'required',
        'reparation_id' => 'required',
    ];

    public function reparation()
    {
        return $this->belongsTo(Reparation::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function equipements()
    {
        return $this->belongsToMany(Fourniture::class, 'fournitures_reparations')->withPivot('quantite', 'equipement')->withTimestamps();
    }
}
