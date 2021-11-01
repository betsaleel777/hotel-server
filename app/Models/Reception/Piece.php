<?php

namespace App\Models\Reception;

use Illuminate\Database\Eloquent\Model;

class Piece extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nature', 'numero_piece', 'lieu_piece', 'delivre_le', 'expire_le', 'maker', 'entree_pays', 'client',
    ];

    const RULES = [
        'nature' => 'required',
        'numero_piece' => 'required|unique:pieces,numero_piece',
    ];

    public static function regles(int $id)
    {
        return [
            'nature' => 'required',
            'numero_piece' => 'required|unique:pieces,numero_piece,' . $id,
        ];
    }

    public function clientLinked()
    {
        return $this->belongsTo(Client::class, 'client');
    }

    public function dossierVide()
    {
        return empty($this->attributes['nature']) and empty($this->attributes['numero_piece']);
    }
}
