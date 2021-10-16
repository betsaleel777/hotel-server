<?php

namespace App\Models\Stock;

use App\Models\Parametre\Departement;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    /**
     * Create a new Demande model instance.
     *
     * @return void
     */
    public function __construct(array $attributes = array(), bool $code = true)
    {
        parent::__construct($attributes);
        if ($code) {
            $this->genererCode();
        }
        $this->traiter();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    const RULES = [
        'titre' => 'required|max:150|unique:demandes,titre',
        'departement' => 'required',
    ];

    const EN_COURS = 'en cours';
    const REJETTEE = 'rejettée';
    const LIVREE = 'livrée';
    const CONFIRMEE = 'confirmée';

    public static function regles(int $id)
    {
        return [
            'titre' => 'required|max:150|unique:demandes,titre,' . $id,
            'departement' => 'required',
        ];
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function confirmer()
    {
        $this->attributes['status'] = self::CONFIRMEE;
    }

    public function traiter()
    {
        $this->attributes['status'] = self::EN_COURS;
    }

    public function rejetter()
    {
        $this->attributes['status'] = self::REJETTEE;
    }

    public function livrer()
    {
        $this->attributes['status'] = self::LIVREE;
    }

    public function scopeSorties($query)
    {
        return $query->where('status', 'livrée');
    }

    public function departementLinked()
    {
        return $this->belongsTo(Departement::class, 'departement');
    }

    public function sortie()
    {
        return $this->hasOne(Sortie::class, 'demande');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'produits_demandes', 'demande', 'produit')->withPivot('quantite')->withTimestamps();
    }
}
