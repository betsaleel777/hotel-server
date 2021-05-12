<?php

namespace App\Models\Reception;

use App\Models\GestionChambre\Chambre;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'accompagnants', 'entree', 'sortie', 'destination', 'chambre', 'client', 'status',
    ];

    const RULES = [
        'accompagnants' => 'nullable|numeric',
        'entree' => 'required',
        'sortie' => 'required',
        'chambre' => 'required',
        'client' => 'required',
    ];
    const RESERVEE = 'reservée';
    const OCCUPEE = 'occupée';
    const ANNULEE = 'annulée';
    const TERMINEE = 'terminée';

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function reserver()
    {
        $this->attributes['status'] = self::RESERVEE;
    }

    public function occuper()
    {
        $this->attributes['status'] = self::OCCUPEE;
    }

    public function terminer()
    {
        $this->attributes['status'] = self::TERMINEE;
    }

    public function annuler()
    {
        $this->attributes['status'] = self::ANNULEE;
    }

    public function scopeReserved($query)
    {
        return $query->where('status', self::RESERVEE);
    }

    public function chambreLinked()
    {
        return $this->belongsTo(Chambre::class, 'chambre');
    }

    public function clientLinked()
    {
        return $this->belongsTo(Client::class, 'client');
    }

    public function attribution()
    {
        return $this->belongsTo(Attribution::class, 'reservation');
    }

}
