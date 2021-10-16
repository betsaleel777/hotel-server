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
    protected $guarded = [];

    protected $table = 'mobile_money';

    const RULES = [
        'nom' => 'required|unique:mobile_money,nom',
    ];

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:mobile_money,nom,' . $id,
        ];
    }

    public function moyenMobile()
    {
        return $this->hasMany(Versement::class, 'mobile_money');
    }

}
