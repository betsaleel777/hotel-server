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

    public function getTourneesProducts()
    {
        $produits = Produit::with('categorieLinked')->tournable()->get();
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

        $message = "le produit $produit->nom a  été crée avec succès.";
        $produit = Produit::with('categorieLinked')->find($produit->id);
        return response()->json(['message' => $message]);
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
        $produit->pour_tournee = $request->pour_tournee;
        $produit->mode = $request->mode;
        $produit->type = $request->type;
        $produit->etagere = $request->etagere;
        $produit->description = $request->description;
        $produit->categorie = $request->categorie;
        $produit->save();

        //enregistrement dans la table des prix
        $prix = new Prix(['montant' => $request->montant, 'produit' => $produit->id]);
        $prix->save();

        $message = "le produit a été modifié avec succès.";
        $produit = Produit::with(['categorieLinked'])->find($produit->id);
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $produit = Produit::find($id);
        $produit->delete();
        $message = "le produit $produit->nom a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'produit' => ['id' => $produit->id, 'code' => $produit->code]]);
    }

    public function inventaireBar()
    {

    }

    public function inventaireRestau()
    {

    }

    public function inventaire()
    {
        $articles = DB::select(DB::raw('WITH stock AS (SELECT IFNULL(sum(a.prix_achat),0) as revient,IFNULL(sum(a.quantite),0) as entree,p.id,p.nom,p.mesure from approvisionements a
right join produits p on p.id=a.ingredient GROUP BY p.id),
sortie as (select IFNULL(sum(ps.recues),0) as sortie,p.id,p.nom,p.mesure from produits_sorties ps
inner join produits p on p.id=ps.produit group by ps.produit)
SELECT s1.id,s1.revient,s1.nom,s1.mesure,s1.entree,IFNULL(s2.sortie,0) as sortie,s1.entree-IFNULL(s2.sortie,0) as disponible from stock s1 left join sortie s2 on s1.id=s2.id'));
        return response()->json(['disponibles' => $articles]);
    }
}
