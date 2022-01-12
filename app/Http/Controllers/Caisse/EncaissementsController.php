<?php
namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Caisse\Encaissement;
use App\Models\Caisse\Versement;
use App\Models\Parametre\Departement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncaissementsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getAll()
    {
        $encaissements = Encaissement::with('attributionLinked.chambreLinked', 'attributionLinked.clientLinked',
            'versements.mobile', 'produits', 'plats', 'cocktails', 'tournees')->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function getByDepartement(int $id)
    {
        $encaissements = Encaissement::with('attributionLinked.chambreLinked', 'attributionLinked.clientLinked',
            'versements.mobile', 'produits', 'plats', 'cocktails', 'tournees')
            ->where('departement', $id)->unpayed()->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function getSoldesByDepartement(int $id)
    {
        $encaissements = Encaissement::doesntHave('attributionLinked')->with('versements.mobile', 'produits', 'plats', 'cocktails', 'tournees')
            ->where('departement', $id)->payed()->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    //création de facture
    public function insert(Request $request)
    {
        $encaissement = new Encaissement($request->all());
        $encaissement->impayer();
        $encaissement->save();
        if (isset($request->boissons)) {
            foreach ($request->boissons as $article) {
                $encaissement->produits()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
            }
        }
        if (isset($request->plats)) {
            foreach ($request->plats as $article) {
                $encaissement->plats()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
            }
        }
        if (isset($request->tournees)) {
            foreach ($request->tournees as $article) {
                $encaissement->tournees()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
            }
        }
        if (isset($request->cocktails)) {
            foreach ($request->cocktails as $article) {
                $encaissement->cocktails()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
            }
        }
        $message = "La caisse a enregistrée avec succès la consommation, code: $encaissement->nom";
        $encaissement = Encaissement::with('plats', 'produits', 'cocktails', 'tournees')->find($encaissement->id);
        return response()->json(['message' => $message]);
    }

    public function encaisser(Request $request)
    {
        $this->validate($request, Versement::RULES);
        $encaissement = Encaissement::with('attributionLinked.clientLinked')->find($request->encaissement);
        if ((int) $request->dejaVerse < (int) $request->montantApayer) {
            $encaissement->impayer();
        } else {
            $encaissement->payer();
            $encaissement->date_soldee = Carbon::now();
        }
        $client = $encaissement->attribution_linked ? $encaissement->attribution_linked->client_linked->nom+' '+$encaissement->attribution_linked->client_linked->prenom : 'Anonyme';
        $encaissement->save();
        $versement = new Versement($request->all());
        $versement->save();
        return response()->json([
            'message' => "Le paiement de la facture $encaissement->code du client $client a été enregistré avec succès.",
        ]);
    }

    public function getOne(int $id)
    {
        $encaissement = Encaissement::with('attributionLinked.chambreLinked', 'attributionLinked.clientLinked',
            'versements.mobile', 'produits', 'plats', 'cocktails', 'tournees')->find($id);
        return response()->json(['encaissement' => $encaissement]);
    }

    public function update(int $id, Request $request)
    {
        $encaissement = Encaissement::find($id);
        $toSync = [];
        foreach ($request->plats as $article) {
            $toSync[$article['id']] = ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']];
        }
        $encaissement->plats()->sync($toSync);
        $toSync = [];
        foreach ($request->boissons as $article) {
            $toSync[$article['id']] = ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']];
        }
        $encaissement->produits()->sync($toSync);
        $toSync = [];
        foreach ($request->cocktails as $article) {
            $toSync[$article['id']] = ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']];
        }
        $encaissement->cocktails()->sync($toSync);
        $toSync = [];
        foreach ($request->tournees as $article) {
            $toSync[$article['id']] = ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']];
        }
        $encaissement->tournees()->sync($toSync);
        $message = "L'encaissement $encaissement->code a été completé avec succès.";
        $encaissement = Encaissement::with('plats', 'produits', 'cocktails', 'tournees')->find($encaissement->id);
        return response()->json(['message' => $message]);
    }

    public function delete()
    {

    }

    public function pointFinancierStandard(int $departement)
    {
        $departementConcerne = Departement::find($departement);
        if ($departementConcerne->nom === 'bar') {
            $articlesVendus = DB::select(DB::Raw(
                "select p.code,p.id,p.nom,p.mesure,'produit' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from produits_encaissements pe inner join produits p
                 on p.id=pe.produit inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 group by p.id,p.code,p.nom,p.mesure
                 UNION
                 select c.code,c.id,c.nom,'' as mesure,'cocktail' as type,AVG(ce.prix_vente) as prix,sum(ce.quantite) as quantite from cocktails_encaissements ce inner join cocktails c
                 on c.id=ce.cocktail inner join encaissements e on e.id=ce.encaissement where e.departement = $departement and e.status='payé'
                 group by c.id,c.code,c.nom
                 UNION
                 select t.code,t.id,t.titre as nom,'' as mesure,'tournee' as type,AVG(te.prix_vente) as prix,sum(te.quantite) as quantite from tournees_encaissements te inner join tournees t
                 on t.id=te.tournee inner join encaissements e on e.id=te.encaissement where e.departement = $departement and e.status='payé'
                 group by t.id,t.code,t.titre"
            ));
            return response()->json(['point' => $articlesVendus]);
        } else {
            $platVendus = DB::select(DB::Raw(
                "select p1.code,p1.id, p1.nom as nom,'' as mesure,'plat' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from plats_encaissements pe inner join plats p1
                 on p1.id=pe.plat inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 group by p1.id,p1.code,p1.nom
                 UNION
                 select p.code,p.id,p.nom,p.mesure,'produit' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from produits_encaissements pe inner join produits p
                 on p.id=pe.produit inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 group by p.id,p.code,p.nom,p.mesure"
            ));
            $point = [];
            return response()->json(['point' => $platVendus]);
        }

    }

    public function pointFinancierIntervalleDate(string $debut, string $fin, int $departement)
    {
        //date format created_at
        $departementConcerne = Departement::find($departement);
        if ($departementConcerne->nom === 'bar') {
            $articlesVendus = DB::select(DB::Raw(
                "select p.code,p.id,p.nom,p.mesure,'produit' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from produits_encaissements pe inner join produits p
                 on p.id=pe.produit inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') between $debut and $fin group by p.id,p.code,p.nom,p.mesure
                 UNION
                 select c.code,c.id,c.nom,'' as mesure,'cocktail' as type,AVG(ce.prix_vente) as prix,sum(ce.quantite) as quantite from cocktails_encaissements ce inner join cocktails c
                 on c.id=ce.cocktail inner join encaissements e on e.id=ce.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') between $debut and $fin group by c.id,c.code,c.nom
                 UNION
                 select t.code,t.id,t.titre as nom,'' as mesure,'tournee' as type,AVG(te.prix_vente) as prix,sum(te.quantite) as quantite from tournees_encaissements te inner join tournees t
                 on t.id=te.tournee inner join encaissements e on e.id=te.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') between $debut and $fin group by t.id,t.code,t.titre"
            ));
            return response()->json(['point' => $articlesVendus]);
        } else {
            $platVendus = DB::select(DB::Raw(
                "select p1.code,p1.id, p1.nom as nom,'' as mesure,'plat' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from plats_encaissements pe inner join plats p1
                 on p1.id=pe.plat inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') between $debut and $fin group by p1.id,p1.code,p1.nom
                 UNION
                 select p.code,p.id,p.nom,p.mesure,'produit' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from produits_encaissements pe inner join produits p
                 on p.id=pe.produit inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') between $debut and $fin group by p.id,p.code,p.nom,p.mesure"
            ));
            return response()->json(['point' => $platVendus]);
        }
    }

    public function pointFinancierJournalier(string $jour, int $departement)
    {
        $departementConcerne = Departement::find($departement);
        if ($departementConcerne->nom === 'bar') {
            $articlesVendus = DB::select(DB::Raw(
                "select p.code,p.id,p.nom,p.mesure,'produit' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from produits_encaissements pe inner join produits p
                 on p.id=pe.produit inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') = $jour group by p.id,p.code,p.nom,p.mesure
                 UNION
                 select c.code,c.id,c.nom,'' as mesure,'cocktail' as type,AVG(ce.prix_vente) as prix,sum(ce.quantite) as quantite from cocktails_encaissements ce inner join cocktails c
                 on c.id=ce.cocktail inner join encaissements e on e.id=ce.encaissement e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') = $jour group by c.id,c.code,c.nom
                 UNION
                 select t.code,t.id,t.titre as nom,'' as mesure,'tournee' as type,AVG(te.prix_vente) as prix,sum(te.quantite) as quantite from tournees_encaissements te inner join tournees t
                 on t.id=te.tournee inner join encaissements e on e.id=te.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') = $jour group by t.id,t.code,t.titre"
            ));
            return response()->json(['point' => $articlesVendus]);
        } else {
            $platVendus = DB::select(DB::Raw(
                "select p1.code,p1.id, p1.nom as nom,'' as mesure,'plat' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from plats_encaissements pe inner join plats p1
                 on p1.id=pe.plat inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') = $jour group by p1.id,p1.code,p1.nom
                 UNION
                 select p.code,p.id,p.nom,p.mesure,'produit' as type,AVG(pe.prix_vente) as prix,sum(pe.quantite) as quantite from produits_encaissements pe inner join produits p
                 on p.id=pe.produit inner join encaissements e on e.id=pe.encaissement where e.departement = $departement and e.status='payé'
                 and DATE_FORMAT(e.created_at,'%d-%m-%Y') = $jour group by p.id,p.code,p.nom,p.mesure"
            ));
            return response()->json(['point' => $platVendus]);
        }

    }
}
