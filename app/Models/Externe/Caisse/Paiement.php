<?php

namespace App\Models\Externe\Caisse;

use App\Models\Externe\Parametre\Mobile;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $guarded = [];

    protected $table = 'paiements_externes';

    const RULES = [
        'montant' => 'required|numeric|min:100',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function moyen()
    {
        return $this->belongsTo(Mobile::class);
    }
}
