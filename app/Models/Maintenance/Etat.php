<?php

namespace App\Models\Maintenance;

use App\Models\GestionChambre\Chambre;
use Illuminate\Database\Eloquent\Model;

class Etat extends Model
{
    protected $fillable = ['fourniture_id', 'chambre_id', 'bon', 'acceptable', 'vetuste'];

    const RULES = [
        'fourniture_id' => 'required',
        'chambre_id' => 'required',
    ];

    public function fourniture()
    {
        return $this->BelongsTo(Fourniture::class);
    }

    public function chambre()
    {
        return $this->belongsTo(Chambre::class);
    }
}
