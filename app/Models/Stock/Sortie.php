<?php

namespace App\Models\Stock;

use App\Models\Parametre\Departement;
use Illuminate\Database\Eloquent\Model;

class Sortie extends Model
{
    /**
     * Create a new Demande model instance.
     *
     * @return void
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->genererCode();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'demande', 'titre', 'departement',
    ];
    const RULES = [
        'titre' => 'required|max:150|unique:sorties,titre',
        'departement' => 'required',
    ];

    public static function regles(int $id)
    {
        return [
            'titre' => 'required|max:150|unique:sorties,titre,' . $id,
        ];
    }

    public function titrer($titre)
    {
        $this->attributes['titre'] = $titre . ' (sortie)';
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function departementLinked()
    {
        return $this->belongsTo(Departement::class, 'departement');
    }

    public function demandeLinked()
    {
        return $this->belongsTo(Demande::class, 'demande');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'produits_sorties', 'sortie', 'produit')->withPivot('demandees', 'quantite', 'recues')->withTimestamps();
    }
}
