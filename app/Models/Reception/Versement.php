<?php

namespace App\Models\Reception;

use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'montant', 'encaissement',
    ];

    const RULES = [
        'montant' => 'required',
    ];

    public function encaissementLinked()
    {
        return $this->belongsTo(Encaissement::class, 'encaissement');
    }
}
