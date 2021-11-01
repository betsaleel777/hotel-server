<?php

namespace App\Models\Externe\Caisse;

use App\Models\Externe\Stock\Article\Article;
use App\Models\Externe\Stock\Cocktail\Cocktail;
use App\Models\Externe\Stock\Plat\Plat;
use App\Models\Externe\Stock\Tournee\Tournee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $table = 'factures_externes';
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

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'factures_articles_externes')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'factures_plats_externes')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function cocktails()
    {
        return $this->belongsToMany(Cocktail::class, 'factures_cocktails_externes')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function tournees()
    {
        return $this->belongsToMany(Tournee::class, 'factures_tournees_externes')->withPivot('quantite', 'prix_vente')->withTimestamps();
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
