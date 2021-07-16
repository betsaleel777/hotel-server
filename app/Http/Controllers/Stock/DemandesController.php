<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Departement;
use App\Models\Stock\Demande;
use App\Models\Stock\Sortie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    private static function checkDemande(array $articles, $sujet = 'demande')
    {
        $ids = array_column($articles, 'id');
        $badProduct = null;
        $achats = DB::select(DB::Raw(
            "SELECT IFNULL(AVG(a.prix_achat),0)  AS prix,IFNULL(SUM(a.quantite),0) AS quantite,p.id,p.nom
             FROM approvisionements AS a RIGHT JOIN produits AS p ON p.id=a.ingredient  WHERE p.id
             IN (" . implode(',', $ids) . ") GROUP BY a.ingredient,p.id,p.nom"
        ));
        foreach ($articles as $article) {
            $casser = true;
            foreach ($achats as $achat) {
                if ($article['id'] === $achat->id) {
                    if ($sujet === 'demande') {
                        $casser = (int) $article['pivot']['quantite'] > (int) $achat->quantite;
                    } else {
                        $casser = (int) $article['quantite'] > (int) $achat->quantite;
                    }
                    if ($casser) {
                        $badProduct = $achat->nom;
                        break;
                    }
                }
            }
            if ($casser) {
                break;
            }
        }
        return $badProduct;
    }

    private static function returning(int $id, string $message)
    {
        $demande = Demande::with('departementlinked', 'produits')->find($id);
        return [
            'message' => $message,
            'demande' => [
                'id' => $demande->id,
                'titre' => $demande->titre,
                'status' => $demande->status,
                'code' => $demande->code,
                'produits' => $demande->produits,
                'created_at' => $demande->created_at,
                'departement' => ['id' => $demande->departementLinked->id, 'nom' => $demande->departementLinked->nom],
            ],
        ];
    }

    public function getAll()
    {
        $demandes = Demande::with('produits', 'departementLinked')->get();
        return response()->json(['demandes' => $demandes]);
    }
    public function getByDepartement(int $departement)
    {
        $demandes = Demande::with('produits', 'departementLinked')->where('departement', $departement)->get();
        return response()->json(['demandes' => $demandes]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Demande::RULES);
        $demande = new Demande($request->all());
        $demande->save();
        foreach ($request->articles as $article) {
            $demande->produits()->attach($article['id'], ['quantite' => $article['quantite']]);
        }
        $message = "La demande, $demande->code a été crée avec succes.";
        return response()->json(self::returning($demande->id, $message));
    }

    public function getOne(int $id)
    {

    }

    public function accept(int $id, Request $request)
    {
        $demande = Demande::find($id);
        $demande->livrer();
        $demande->save();
        $sortie = new Sortie();
        $sortie->titrer($demande->titre);
        $sortie->demande = $demande->id;
        $sortie->departement = $demande->departement;
        $sortie->save();
        foreach ($request->articles as $article) {
            $sortie->produits()->attach($article['produit'], ['quantite' => $article['valeur'], 'demandees' => $article['quantite']]);
        }
        $message = "La demande, $demande->code a été livrée.\n La sortie de stock $sortie->code a été crée avec succès.";
        return response()->json(self::returning($id, $message));
    }

    public function reject(int $id)
    {
        $demande = Demande::find($id);
        $demande->rejetter();
        $demande->save();
        $message = "La demande, $demande->code a été rejetée.";
        return response()->json(self::returning($id, $message));
    }

    public function traitement(int $id)
    {
        $demande = Demande::find($id);
        $articlesWithoutDelivery = DB::select(DB::Raw(
            "WITH sortie AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
             INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
             GROUP BY pe.produit,p.nom,p.code,p.mesure)
             SELECT pd.produit,pd.quantite,p.nom,p.code,p.mesure,SUM(a.quantite - IFNULL(sortie.quantite/2,0)) AS disponible
             FROM produits_demandes pd INNER JOIN produits p ON p.id=pd.produit INNER JOIN approvisionements a ON a.ingredient = pd.produit
             LEFT JOIN sortie ON sortie.produit = p.id WHERE pd.demande = $id GROUP BY a.ingredient,pd.produit,pd.quantite,p.nom,p.code,p.mesure"
        ));
        //tournee encaissée à prendre en compte ici
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
                            'quantite' => $withoutDelivery->quantite,
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
        return response()->json(['articles' => $inventaire]);
    }

    public function getProductsByDepartement(int $departement)
    {
        $produits = DB::select(DB::raw(
            "SELECT p.id,p.nom, p.code, p.mesure FROM produits_sorties ps
             INNER JOIN sorties s ON s.id=ps.sortie INNER JOIN produits p ON p.id=ps.produit
             WHERE s.departement=$departement GROUP BY ps.produit,p.id,p.nom,p.code,p.mesure"
        ));
        return response()->json(['produits' => $produits]);
    }

    public function inventaire(int $departement)
    {
        $departementConcerne = Departement::find($departement);

        if ($departementConcerne->nom === 'bar') {
            $inventaireSansTourneeVendus = DB::select(DB::Raw(
                "WITH encaisse AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
                 INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
                 WHERE e.departement =$departement GROUP BY pe.produit,p.nom,p.code,p.mesure)
                 SELECT p.id as produit,p.nom,p.code,p.mesure,SUM(ps.quantite-IFNULL(encaisse.quantite,0)) AS disponible, s.departement,p.prix_vente
                 ,IFNULL(t.contenance,0) as contenance FROM produits_sorties ps INNER JOIN sorties s ON s.id=ps.sortie
                 INNER JOIN produits p ON p.id=ps.produit LEFT JOIN tournees t ON t.produit =ps.produit
                 LEFT JOIN encaisse ON encaisse.produit = p.id WHERE s.departement=$departement GROUP BY p.id,p.nom,p.code,p.mesure,s.departement,p.prix_vente,t.contenance"
            ));
            $tourneesVendus = DB::select(DB::Raw(
                "with encaisse as (select c.nom,c.id,sum(ce.quantite) as nombre from cocktails_encaissements ce inner join cocktails c on c.id = ce.cocktail
                 group by ce.cocktail,c.nom,c.id)
                 select p.id as produit,t1.titre as nom,sum(ct.quantite*e.nombre)*t1.nombre*25 as consommation
                 from cocktails_tournees ct inner join encaisse e on e.id=ct.cocktail inner join tournees t1 on t1.id=ct.tournee
                 inner join produits p on t1.produit = p.id group by ct.tournee,p.id,t1.titre,t1.nombre
                 UNION
                 select p.id as produit,t.titre as nom,sum(te.quantite)*t.nombre*25 as consommation from tournees_encaissements te
                 inner join tournees t on t.id = te.tournee inner join produits p on t.produit = p.id group by te.tournee,p.id,t.titre,t.nombre"
            ));

            $inventaire = [];
            $ids = array_column($tourneesVendus, 'produit');
            foreach ($inventaireSansTourneeVendus as $sansTournee) {
                if (in_array($sansTournee->produit, $ids)) {
                    foreach ($tourneesVendus as $vendus) {
                        if ($vendus->produit === $sansTournee->produit) {
                            $disponibleFloat = $sansTournee->disponible - ($vendus->consommation / $sansTournee->contenance);
                            $valeurEntiere = intval($disponibleFloat);
                            $decimalPart = $disponibleFloat - $valeurEntiere;
                            $resteBouteille = $decimalPart * 100;
                            $inventaire[] = [
                                'produit' => $vendus->produit,
                                'nom' => $sansTournee->nom,
                                'code' => $sansTournee->code,
                                'mesure' => $sansTournee->mesure,
                                'disponible' => $valeurEntiere,
                                'reste' => round($resteBouteille),
                            ];
                            break;
                        }
                    }
                } else {
                    $inventaire[] = $sansTournee;
                }
            }
            return response()->json(['inventaire' => $inventaire]);
        } else {
            $inventaireSansPlatVendus = DB::select(DB::Raw(
                "WITH encaisse AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
                 INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
                 WHERE e.departement = $departement GROUP BY pe.produit,p.nom,p.code,p.mesure)
                 SELECT p.id as produit,p.nom,p.code,p.mesure,SUM(ps.quantite-IFNULL(encaisse.quantite,0)) AS disponible, s.departement,p.prix_vente
                 FROM produits_sorties ps INNER JOIN sorties s ON s.id=ps.sortie INNER JOIN produits p ON p.id=ps.produit
                 LEFT JOIN encaisse ON encaisse.produit = p.id WHERE s.departement=$departement
                 GROUP BY p.id,p.nom,p.code,p.mesure,s.departement,p.prix_vente"
            ));

            $platsVendus = DB::select(DB::Raw(
                "WITH encaisse AS (SELECT pl.id,pl.nom,sum(pe.quantite) AS nombre FROM plats pl
             INNER JOIN plats_encaissements pe on pe.plat=pl.id GROUP BY pe.plat,pl.id,pl.nom)
             SELECT i.produit,p.code,p.nom,p.mesure,AVG(i.quantite*e.nombre) AS quantite FROM ingredients i
             INNER JOIN produits p on p.id=i.produit INNER JOIN encaisse e ON e.id=i.plat WHERE i.plat IN
             (SELECT pl.id FROM plats pl INNER JOIN plats_encaissements pe ON pe.plat=pl.id GROUP BY pe.plat,pl.id)
             GROUP BY p.id,p.nom,p.mesure,i.produit,p.code"
            ));
            $inventaire = [];
            $ids = array_column($platsVendus, 'produit');
            foreach ($inventaireSansPlatVendus as $sansPlats) {
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

    public function inventaireBuvable(int $departement)
    {
        $inventaire = DB::select(DB::Raw(
            "WITH encaisse AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
             INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
             WHERE e.departement = $departement GROUP BY pe.produit,p.nom,p.code,p.mesure)
             SELECT p.id,p.nom,p.code,p.mesure,SUM(ps.quantite-IFNULL(encaisse.quantite,0)) AS quantite, s.departement,p.prix_vente
             FROM produits_sorties ps INNER JOIN sorties s ON s.id=ps.sortie INNER JOIN produits p ON p.id=ps.produit
             LEFT JOIN encaisse ON encaisse.produit = p.id WHERE s.departement=$departement AND p.pour_plat=0 AND p.pour_tournee=0
             GROUP BY p.id,p.nom,p.code,p.mesure,s.departement,p.prix_vente"
        ));
        // $inventaireSansTourneeVendus = DB::select(DB::Raw(
        //     "WITH encaisse AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
        //      INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
        //      WHERE e.departement = $departement GROUP BY pe.produit,p.nom,p.code,p.mesure)
        //      SELECT p.id as produit,p.nom,p.code,p.mesure,IFNULL(t.contenance,0) as contenance,SUM(ps.quantite-IFNULL(encaisse.quantite,0)) AS disponible, s.departement,p.prix_vente
        //      FROM produits_sorties ps INNER JOIN sorties s ON s.id=ps.sortie INNER JOIN produits p ON p.id=ps.produit
        //      LEFT JOIN tournees t ON t.produit =ps.produit LEFT JOIN encaisse ON encaisse.produit = p.id WHERE s.departement = $departement AND p.pour_plat=0
        //      GROUP BY p.id,p.nom,p.code,p.mesure,s.departement,p.prix_vente,t.contenance"
        // ));
        // $tourneesVendus = DB::select(DB::Raw(
        //     "WITH encaisse AS (SELECT c.nom,c.id,sum(ce.quantite) AS nombre FROM cocktails_encaissements ce inner join cocktails c on c.id = ce.cocktail
        //      group by ce.cocktail,c.nom,c.id)
        //      select p.id as produit,t1.titre as nom,sum(ct.quantite*e.nombre)*t1.nombre*25 as consommation
        //      from cocktails_tournees ct inner join encaisse e on e.id=ct.cocktail inner join tournees t1 on t1.id=ct.tournee
        //      inner join produits p on t1.produit = p.id group by ct.tournee,p.id,t1.titre,t1.nombre
        //      UNION
        //      select p.id as produit,t.titre as nom,sum(te.quantite)*t.nombre*25 as consommation from tournees_encaissements te
        //      inner join tournees t on t.id = te.tournee inner join produits p on t.produit = p.id group by te.tournee,p.id,t.titre,t.nombre"
        // ));
        // $inventaire = [];
        // $ids = array_column($tourneesVendus, 'produit');
        // foreach ($inventaireSansTourneeVendus as $sansTournee) {
        //     if (in_array($sansTournee->produit, $ids)) {
        //         foreach ($tourneesVendus as $vendus) {
        //             if ($vendus->produit === $sansTournee->produit) {
        //                 $disponibleFloat = $sansTournee->disponible - ($vendus->consommation / $sansTournee->contenance);
        //                 $valeurEntiere = intval($disponibleFloat);
        //                 $decimalPart = $disponibleFloat - $valeurEntiere;
        //                 $resteBouteille = $decimalPart * 100;
        //                 $inventaire[] = [
        //                     'produit' => $vendus->produit,
        //                     'nom' => $sansTournee->nom,
        //                     'code' => $sansTournee->code,
        //                     'mesure' => $sansTournee->mesure,
        //                     'disponible' => $valeurEntiere,
        //                     'reste' => round($resteBouteille),
        //                 ];
        //                 break;
        //             }
        //         }
        //     } else {
        //         $inventaire[] = $sansTournee;
        //     }
        // }
        return response()->json(['inventaire' => $inventaire]);
    }

}

// WITH encaisse AS (SELECT pe.produit,p.nom,p.code,p.mesure,SUM(pe.quantite) AS quantite FROM produits_encaissements pe
// INNER JOIN produits p ON p.id=pe.produit INNER JOIN encaissements e ON e.id = pe.encaissement
// WHERE e.departement =$departement GROUP BY pe.produit,p.nom,p.code,p.mesure)
// SELECT p.id as produit,p.nom,p.code,p.mesure,SUM(ps.quantite-IFNULL(encaisse.quantite,0)) AS disponible, s.departement,p.prix_vente
// ,IFNULL(t.contenance,0) as contenance FROM produits_sorties ps INNER JOIN sorties s ON s.id=ps.sortie
// INNER JOIN produits p ON p.id=ps.produit LEFT JOIN tournees t ON t.produit =ps.produit
// LEFT JOIN encaisse ON encaisse.produit = p.id WHERE s.departement=$departement GROUP BY p.id,p.nom,p.code,p.mesure,s.departement,p.prix_vente,t.contenance
