<?php

namespace App\Models\Caisse;

use App\Models\Bar\Cocktail;
use App\Models\Bar\Tournee;
use App\Models\Reception\Attribution;
use App\Models\Restaurant\Plat;
use App\Models\Stock\Produit;
use Illuminate\Database\Eloquent\Model;

class Encaissement extends Model
{
    /**
     * Create a new controller instance.
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
    protected $guarded = [];
    protected $dates = ['date_soldee', 'created_at'];
    const PAYER = 'payé';
    const IMPAYER = 'impayé';

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function payer()
    {
        $this->attributes['status'] = self::PAYER;
    }

    public function impayer()
    {
        $this->attributes['status'] = self::IMPAYER;
    }

    public function scopeUnpayed($query)
    {
        return $query->where('status', self::IMPAYER);
    }

    public function scopePayed($query)
    {
        return $query->where('status', self::PAYER);
    }

    public function attributionLinked()
    {
        return $this->belongsTo(Attribution::class, 'attribution');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'produits_encaissements', 'encaissement', 'produit')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'plats_encaissements', 'encaissement', 'plat')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function cocktails()
    {
        return $this->belongsToMany(Cocktail::class, 'cocktails_encaissements', 'encaissement', 'cocktail')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function tournees()
    {
        return $this->belongsToMany(Tournee::class, 'tournees_encaissements', 'encaissement', 'tournee')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function versements()
    {
        return $this->hasMany(Versement::class, 'encaissement');
    }
}
