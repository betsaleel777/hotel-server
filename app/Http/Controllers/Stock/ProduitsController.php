<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Categorie;
use App\Models\Stock\Prix;
use App\Models\Stock\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduitsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    const PRODUIT_STANDARD = 'standard';
    const PRODUIT_ASSAISONEMENT = 'assaisonement';

    public function getAll()
    {
        $produits = Produit::with('categorieLinked')->get();
        return response()->json(['produits' => $produits]);
    }

    public function getPlatProducts()
    {
        $produits = Produit::with('categorieLinked')->cuisinable()->get();
        return response()->json(['produits' => $produits]);
    }

    public function getBoissonProducts()
    {
        $produits = Produit::with('categorieLinked')->buvable()->get();
        return response()->json(['produits' => $produits]);
    }

    public function insert(Request $request)
    {
        $request->type === self::PRODUIT_STANDARD ? $rules = array_merge(Produit::RULES, Categorie::RULES, Prix::RULES) : $rules = array_merge(Produit::RULES, Categorie::RULES);
        $this->validate($request, $rules);
        $produit = new Produit($request->except('image'));
        $produit->prix_vente = $request->montant;
        $produit->genererCode();
        $produit->save();

        //enregistrement dans la table des prix
        $prix = new Prix(['montant' => $request->montant, 'produit' => $produit->id]);
        $prix->save();

        $message = "le produit $produit->code a  été crée avec succès.";
        $produit = Produit::with('categorieLinked')->find($produit->id);
        return response()->json([
            'message' => $message,
            'produit' => [
                'id' => $produit->id,
                'code' => $produit->code,
                'nom' => $produit->nom,
                'mesure' => $produit->mesure,
                'image' => [],
                'mode' => $produit->mode,
                'type' => $produit->type,
                'montant' => $produit->prix_vente,
                'pour_plat' => $produit->pour_plat,
                'description' => $produit->description,
                'etagere' => $produit->etagere,
                'categorie' => ['id' => $produit->categorieLinked->id, 'nom' => $produit->categorieLinked->nom],
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $produit = Produit::find($id);
        return response()->json(['produit' => $produit]);
    }

    public function update(int $id, Request $request)
    {
        $request->type === self::PRODUIT_STANDARD ? $rules = array_merge(Produit::RULES, Categorie::RULES, Prix::RULES) : $rules = array_merge(Produit::RULES, Categorie::RULES);
        $this->validate($request, $rules);
        $produit = Produit::find($id);
        $produit->nom = $request->nom;
        $produit->mesure = $request->mesure;
        $produit->prix_vente = $request->montant;
        $produit->pour_plat = $request->pour_plat;
        $produit->mode = $request->mode;
        $produit->type = $request->type;
        $produit->etagere = $request->etagere;
        $produit->description = $request->description;
        $produit->categorie = $request->categorie;
        $produit->save();

        //enregistrement dans la table des prix
        $prix = new Prix(['montant' => $request->montant, 'produit' => $produit->id]);
        $prix->save();

        $message = "le produit $produit->code a  été modifié avec succès.";
        $produit = Produit::with(['categorieLinked'])->find($produit->id);
        return response()->json([
            'message' => $message,
            'produit' => [
                'id' => $produit->id,
                'code' => $produit->code,
                'nom' => $produit->nom,
                'mesure' => $produit->mesure,
                'image' => [],
                'mode' => $produit->mode,
                'type' => $produit->type,
                'montant' => $produit->prix_vente,
                'pour_plat' => $produit->pour_plat,
                'description' => $produit->description,
                'etagere' => $produit->etagere,
                'categorie' => ['id' => $produit->categorieLinked->id, 'nom' => $produit->categorieLinked->nom],
            ],
        ]);
    }

    public function delete(int $id)
    {
        $produit = Produit::find($id);
        $produit->delete();
        $message = "le produit $produit->code a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'produit' => ['id' => $produit->id, 'code' => $produit->code]]);
    }

    public function inventaire()
    {
        $inventaireSanslesPlatVendus = DB::select(DB::Raw(
            "WITH sortie AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
             INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
             GROUP BY pe.produit,p.nom,p.code,p.mesure)
             SELECT p.id as produit,p.nom,p.code,p.mesure,IFNULL(SUM(a.quantite-IFNULL(sortie.quantite/2,0)),0) AS disponible,p.prix_vente
             FROM produits p LEFT JOIN approvisionements a ON a.ingredient=p.id LEFT JOIN sortie ON sortie.produit = p.id
             GROUP BY a.ingredient,p.nom,p.code,p.mesure,p.prix_vente,p.id"
        ));
        $platsVendus = DB::select(DB::Raw(
            "with sortie as (select pl.id,pl.nom,sum(pe.quantite) as nombre from plats pl
             inner join plats_encaissements pe on pe.plat=pl.id group by pe.plat,pl.id,pl.nom)
             select i.produit,p.code,p.nom,p.mesure,AVG(i.quantite*s.nombre) as quantite from ingredients i inner join produits p on p.id=i.produit
             inner join sortie s on s.id=i.plat where i.plat in (select pl.id from plats pl inner join plats_encaissements pe on pe.plat=pl.id group by pe.plat,pl.id)
             group by p.id,p.nom,p.mesure,i.produit,p.code"
        ));
        $inventaire = [];
        $ids = array_column($platsVendus, 'produit');
        foreach ($inventaireSanslesPlatVendus as $sansPlats) {
            if (in_array($sansPlats->produit, $ids)) {
                foreach ($platsVendus as $vendus) {
                    if ($vendus->produit === $sansPlats->produit) {
                        $inventaire[] = [
                            'produit' => $vendus->produit,
                            'nom' => $vendus->nom,
                            'code' => $vendus->code,
                            'mesure' => $vendus->mesure,
                            'disponible' => $sansPlats->disponible - $vendus->quantite,
                        ];
                        break;
                    }
                }
            } else {
                $inventaire[] = $sansPlats;
            }
        }
        return response()->json(['inventaire' => $inventaire]);
    }

    public function inventaireSortie()
    {
        $articlesWithoutDelivery = DB::select(DB::Raw(
            "WITH sortie AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
             INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
             GROUP BY pe.produit,p.nom,p.code,p.mesure)
             SELECT p.id as produit,p.nom,p.code,p.mesure,IFNULL(SUM(a.quantite-IFNULL(sortie.quantite/2,0)),0) AS disponible,p.prix_vente
             FROM produits p LEFT JOIN approvisionements a ON a.ingredient=p.id LEFT JOIN sortie ON sortie.produit = p.id
             GROUP BY a.ingredient,p.nom,p.code,p.mesure,p.prix_vente,p.id"
        ));
        $articlesDelivered = DB::select(DB::Raw(
            "SELECT ps.produit,p.nom,p.code,p.mesure,SUM(ps.quantite) AS quantite FROM produits_sorties ps
             INNER JOIN produits p ON p.id=ps.produit GROUP BY ps.produit,p.nom,p.code,p.mesure"
        ));
        $articles = [];
        $produitsDelivered = array_column($articlesDelivered, 'produit');
        foreach ($articlesWithoutDelivery as $withoutDelivery) {
            if (in_array($withoutDelivery->produit, $produitsDelivered)) {
                foreach ($articlesDelivered as $delivered) {
                    if ($delivered->produit === $withoutDelivery->produit) {
                        $articles[] = (object) [
                            'produit' => $delivered->produit,
                            'nom' => $delivered->nom,
                            'code' => $delivered->code,
                            'mesure' => $delivered->mesure,
                            'disponible' => $withoutDelivery->disponible - $delivered->quantite,
                        ];
                        break;
                    }
                }
            } else {
                $articles[] = $withoutDelivery;
            }
        }
        $platsVendus = DB::select(DB::Raw(
            "WITH sortie AS (SELECT pl.id,pl.nom,sum(pe.quantite) AS nombre FROM plats pl
             INNER JOIN plats_encaissements pe on pe.plat=pl.id GROUP BY pe.plat,pl.id,pl.nom)
             SELECT i.produit,p.code,p.nom,p.mesure,AVG(i.quantite*s.nombre) AS quantite FROM ingredients i
             INNER JOIN produits p on p.id=i.produit INNER JOIN sortie s ON s.id=i.plat WHERE i.plat IN
             (SELECT pl.id FROM plats pl INNER JOIN plats_encaissements pe ON pe.plat=pl.id GROUP BY pe.plat,pl.id)
             GROUP BY p.id,p.nom,p.mesure,i.produit,p.code"
        ));
        $inventaire = [];
        $ids = array_column($platsVendus, 'produit');
        foreach ($articles as $sansPlats) {
            if (in_array($sansPlats->produit, $ids)) {
                foreach ($platsVendus as $vendus) {
                    if ($vendus->produit === $sansPlats->produit) {
                        $inventaire[] = [
                            'produit' => $vendus->produit,
                            'nom' => $vendus->nom,
                            'code' => $vendus->code,
                            'mesure' => $vendus->mesure,
                            'disponible' => $sansPlats->disponible - $vendus->quantite,
                        ];
                        break;
                    }
                }
            } else {
                $inventaire[] = $sansPlats;
            }
        }
        return response()->json(['inventaire' => $inventaire]);
    }
}
