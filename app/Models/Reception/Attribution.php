<?php

namespace App\Models\Reception;

use App\Models\Caisse\Encaissement as EncaissementCaisse;
use App\Models\GestionChambre\Chambre;
use Illuminate\Database\Eloquent\Model;

class Attribution extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $dates = ['entree', 'sortie', 'date_liberation'];
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

    public function scopeFree($query)
    {
        return $query->where('status', self::LIBEREE);
    }

    public function scopeBusy($query)
    {
        return $query->where('status', self::OCCUPEE);
    }

    public function scopeBusyFree($query)
    {
        return $query->orWhere('status', self::OCCUPEE)->orWhere('status', self::LIBEREE);
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

    public function consommations()
    {
        return $this->hasMany(EncaissementCaisse::class, 'attribution');
    }

    public function encaissement()
    {
        return $this->hasOne(Encaissement::class, 'attribution');
    }

}
