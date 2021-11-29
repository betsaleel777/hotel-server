<?php

namespace App\Models\Maintenance;

use App\Models\Reception\Attribution;

class EtatApresAttribution extends Etat
{
    protected $table = 'etat_apres_attributions';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        array_merge($this->fillable, ['attribution_id']);
    }

    public function attribution()
    {
        return $this->belongsTo(Attribution::class);
    }
}
