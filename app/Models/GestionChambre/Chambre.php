<?php

namespace App\Models\GestionChambre;

use App\Models\Maintenance\Fourniture;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chambre extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom', 'categorie', 'status', 'code', 'prix_vente',
    ];
    protected $dates = ['created_at'];

    const RULES = [
        'nom' => 'required|unique:chambres,nom',
        'categorie' => 'required',
    ];

    const OCCUPEE = 'occupée';
    const LIBRE = 'libre';
    const MAINTENANCE = 'en maintenance';

    public static function regles(int $id)
    {
        return [
            'nom' => 'required|unique:chambres,nom,' . $id,
            'categorie' => 'required',
        ];
    }

    public function liberer()
    {
        $this->attributes['status'] = self::LIBRE;
    }

    public function occuper()
    {
        $this->attributes['status'] = self::OCCUPEE;
    }

    public function maintenance()
    {
        $this->attributes['status'] = self::MAINTENANCE;
    }

    public function genererCode()
    {
        $chiffres = '0123456789';
        $lettres = 'abcdefghijklmnopqrstuvwxyz';
        $this->attributes['code'] = strtoupper(str_shuffle(substr(str_shuffle($lettres), 0, 4) . substr(str_shuffle($chiffres), 0, 3)));
    }

    public function scopeLibre($query)
    {
        return $query->where('status', self::LIBRE);
    }

    public function categorieLinked()
    {
        return $this->belongsTo(CategorieChambre::class, 'categorie');
    }

    public function prixList()
    {
        return $this->hasMany(PrixChambre::class, 'chambre');
    }

    public function equipements()
    {
        return $this->belongsToMany(Fourniture::class, 'etats')->withPivot('libelle', 'quantite')->withTimestamps();
    }
}
