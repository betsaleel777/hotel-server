<?php

namespace App\Models\Caisse;

use App\Models\Reception\Versement;
use Illuminate\Database\Eloquent\Model;

class MobileMoney extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom',
    ];

    protected $table = 'mobile_money';

    public function paiementReservation()
    {
        return $this->hasMany(Versement::class, 'mobile_money');
    }

}
