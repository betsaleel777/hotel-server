<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['valeur', 'entretien_id'];

    protected $table = 'notes_entretiens';

    const RULES = ['valeur' => 'required', 'description' => 'required'];

    public function entretien()
    {
        return $this->belongsTo(Entretien::class);
    }
}
