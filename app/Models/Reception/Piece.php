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
        'delivre_le' => 'required',
        'expire_le' => 'required',
        'lieu_piece' => 'required',
    ];

    public static function regles(int $id)
    {
        return [
            'nature' => 'required',
            'numero_piece' => 'required|unique:pieces,numero_piece,' . $id,
            'delivre_le' => 'required',
            'expire_le' => 'required',
            'lieu_piece' => 'required',
        ];
    }

    public function clientLinked()
    {
        return $this->belongsTo(Client::class, 'client');
    }

}
