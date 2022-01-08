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

    public function inventaireDepartement(int $departement)
    {
        $articles = DB::select(DB::raw("WITH produit_encaisse as (
with out_tournee as (
with sortie as (SELECT te.tournee,sum(te.quantite) as sortie,avg(te.prix_vente) as vente from tournees_encaissements te
inner join encaissements e on e.id=te.encaissement where e.departement = $departement group by te.tournee)
select p.id as produit,p.mesure,t.id as tournee,p.nom,IFNULL(s.sortie*t.nombre*5,0) as outcontenance,t.contenance
from tournees t left join sortie s on s.tournee=t.id inner join produits p on p.id=t.produit
), out_cocktail as (
with sortie as (SELECT ce.cocktail,sum(ce.quantite) as sortie,avg(ce.prix_vente) as vente from cocktails_encaissements ce
inner join encaissements e on e.id=ce.encaissement where e.departement = $departement group by ce.cocktail),
drink as (select c.id,ct.quantite,ct.tournee,t.nombre,t.titre,t.contenance from cocktails c inner join cocktails_tournees ct on c.id=ct.cocktail inner join tournees t on t.id=ct.tournee)
select d.tournee,d.titre,IFNUll(sum(s.sortie*d.quantite*d.nombre*5),0) as outcontenance,d.contenance from drink d left join sortie s on s.cocktail=d.id group by d.tournee
),
sortie as (SELECT pe.plat,sum(pe.quantite) as sortie,avg(pe.prix_vente) as vente from plats_encaissements pe
inner join encaissements e on e.id=pe.encaissement where e.departement = $departement group by pe.plat),
food as (select p.id,i.quantite,p1.mesure,p1.nom,i.produit from plats p inner join ingredients i on p.id=i.plat inner join produits p1 on p1.id=i.produit)
select ot.produit as id,ot.nom,IFNULL((ot.outcontenance+oc.outcontenance) DIV oc.contenance,0) as outstock,ot.mesure,IFNULL(NULLIF(100-(ot.outcontenance+oc.outcontenance) MOD oc.contenance*100/oc.contenance,100),0) as reste
from out_tournee ot inner join out_cocktail oc on oc.tournee=ot.tournee
UNION
select f.produit as id,f.nom,IFNUll(sum(s.sortie*f.quantite),0) as outstock,f.mesure,0 as reste from food f left join sortie s on s.plat=f.id group by f.produit
UNION
select p.id,p.nom,sum(pe.quantite) as outstock,p.mesure,0 as reste from produits p left join produits_encaissements pe on pe.produit=p.id
inner join encaissements e on e.id=pe.encaissement where p.pour_plat = false and p.pour_tournee = false and e.departement = $departement group by pe.produit
),produit_demande as (
select p.id,p.nom,IFNULL(sum(ps.recues),0) as instock,p.mesure from produits_sorties ps right join produits p on ps.produit=p.id
inner join sorties s on s.id=ps.sortie inner join demandes d on d.id=s.demande where d.status='confirmée' and d.departement = $departement group by ps.produit
)
select pd.id,pd.nom,pd.instock,pe.outstock,pd.instock-pe.outstock as disponible,pd.mesure,pe.reste from produit_encaisse pe inner join produit_demande pd on pd.id=pe.id "
        ));
        return response()->json(['disponibles' => $articles]);
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
