<?php

namespace App\Models\Reception;

use App\Models\GestionChambre\Chambre;
use Illuminate\Database\Eloquent\Model;

class Attribution extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'accompagnants', 'entree', 'sortie', 'destination', 'chambre', 'client', 'reservation', 'status', 'remise',
    ];

    const RULES = [
        'accompagnants' => 'nullable|numeric',
        'entree' => 'required',
        'sortie' => 'required',
        'chambre' => 'required',
        'client' => 'required',
    ];
    const OCCUPEE = 'occupée';
    const LIBEREE = 'libérée';

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function liberer()
    {
        $this->attributes['status'] = self::LIBEREE;
    }

    public function occuper()
    {
        $this->attributes['status'] = self::OCCUPEE;
    }

    public function scopeIsFree($query)
    {
        return $query->where('status', self::LIBEREE);
    }

    public function scopeIsBusy($query)
    {
        return $query->where('status', self::OCCUPEE);
    }

    public function chambreLinked()
    {
        return $this->belongsTo(Chambre::class, 'chambre');
    }

    public function clientLinked()
    {
        return $this->belongsTo(Client::class, 'client');
    }

    public function reservationLinked()
    {
        return $this->belongsTo(Reservation::class, 'reservation');
    }

}
