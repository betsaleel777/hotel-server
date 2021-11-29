<?php

namespace App\Models\Maintenance;

class EtatApresEntretien extends Etat
{
    protected $table = 'etat_apres_entretiens';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        array_merge($this->fillable, ['entretien_id']);
    }

    public function entretien()
    {
        return $this->belongsTo(Entretien::class);
    }
}
