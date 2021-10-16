<?php

namespace App\Models\GestionChambre;

use Illuminate\Database\Eloquent\Model;

class PrixChambre extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    const RULES = [
        'montant' => 'required|numeric',
    ];

    public function chambreLinked()
    {
        return $this->belongsTo(Chambre::class, 'chambre');
    }
}
