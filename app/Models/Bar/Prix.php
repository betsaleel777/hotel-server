<?php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Model;

class Prix extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'prix_tournees';

    const RULES = [
        'montant' => 'required',
    ];

    public function tourneeLinked()
    {
        return $this->belongsTo(Tournee::class, 'tournee');
    }
}
