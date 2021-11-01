<?php

namespace App\Models\Reception;

use Illuminate\Database\Eloquent\Model;

class Encaissement extends Model
{

    public function __construct(array $attributes = array())
    {
        $this->genererCode();
        $this->en_cours();
        parent::__construct($attributes);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'status', 'attribution', 'reservation', 'precedant', 'date_soldee',
    ];
    protected $dates = ['date_soldee', 'created_at'];
    protected $table = 'encaissements_receptions';

    const SOLDEE = 'soldée';
    const EN_COURS = 'en cours';

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function solder()
    {
        $this->attributes['status'] = self::SOLDEE;
    }

    public function en_cours()
    {
        $this->attributes['status'] = self::EN_COURS;
    }

    public function scopeSoldes($query)
    {
        return $query->where('status', self::SOLDEE);
    }

    public function scopeNonSoldes($query)
    {
        return $query->where('status', self::EN_COURS);
    }

    public function reservationLinked()
    {
        return $this->belongsTo(Reservation::class, 'reservation');
    }

    public function attributionLinked()
    {
        return $this->belongsTo(Attribution::class, 'attribution');
    }

    public function anterieur()
    {
        return $this->belongsTo(Encaissement::class, 'precedant');
    }

    public function versements()
    {
        return $this->hasMany(Versement::class, 'encaissement');
    }

}
