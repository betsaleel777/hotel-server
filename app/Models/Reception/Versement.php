<?php

namespace App\Models\Reception;

use App\Models\Caisse\MobileMoney;
use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'montant', 'encaissement', 'mobile_money', 'espece', 'cheque', 'monnaie',
    ];

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
}
