<?php

namespace App\Models\Caisse;

use App\Models\Parametre\Departement;
use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'montant', 'encaissement', 'mobile_money', 'espece', 'cheque', 'monnaie', 'departement',
    ];

    protected $table = 'versements_departements';

    const RULES = [
        'montant' => 'required', 'monnaie' => 'required',
    ];

    public function encaissementLinked()
    {
        return $this->belongsTo(Encaissement::class, 'encaissement');
    }

    public function mobile()
    {
        return $this->belongsTo(MobileMoney::class, 'mobile_money');
    }

    public function departementLinked()
    {
        return $this->belongsTo(Departement::class, 'departement');
    }
}
