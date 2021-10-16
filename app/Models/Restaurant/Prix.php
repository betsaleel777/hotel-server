<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $table = 'prix_plats';

    const RULES = [
        'achat' => 'required|numeric',
        'vente' => 'required|numeric',
    ];

    public function platLinked()
    {
        return $this->belongsTo(Plat::class, 'plat');
    }
}
