<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
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
                'departement' => $demande->departementLinked->id,
            ],
        ];
    }

    public function getAll()
    {
        $demandes = Demande::with('produits', 'departementLinked')->get();
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
        $sortie = new Sortie(titre:$demande->titre);
        $sortie->demande = $demande->id;
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
        //encaissement à prendre en compte ici
        $articlesWithoutDelivery = DB::select(DB::Raw(
            "SELECT pd.produit,pd.quantite,p.nom,p.code,p.mesure,SUM(a.quantite) AS disponible FROM produits_demandes pd
             INNER JOIN produits p ON p.id=pd.produit INNER JOIN approvisionements a ON a.ingredient = pd.produit
             WHERE pd.demande = $id GROUP BY a.ingredient,pd.produit,pd.quantite,p.nom,p.code,p.mesure"
        ));
        $articlesDelivered = DB::select(DB::Raw(
            "SELECT pd.produit,p.nom,p.code,p.mesure,SUM(pd.quantite) AS quantite FROM motel.produits_demandes pd
             INNER JOIN produits p ON p.id=pd.produit INNER JOIN demandes d ON pd.demande = d.id
             WHERE d.status = 'livrée' GROUP BY pd.produit,p.nom,p.code,p.mesure"
        ));
        $articles = [];
        $produitsDelivered = array_column($articlesDelivered, 'produit');
        foreach ($articlesWithoutDelivery as $withoutDelivery) {
            if (in_array($withoutDelivery->produit, $produitsDelivered)) {
                foreach ($articlesDelivered as $delivered) {
                    if ($delivered->produit === $withoutDelivery->produit) {
                        $articles[] = [
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
        return response()->json(['articles' => $articles]);
    }

    public function deliver(int $id)
    {
        $demande = Demande::find($id);
        $demande->livrer();
        $demande->save();
        $message = "La demande: $demande->code a été livrée avec succès.";
        return response()->json(self::returning($id, $message));
    }

    public function inventaire(int $departement)
    {
        $inventaire = DB::select(DB::Raw(
            "SELECT p.id,p.nom, p.code, p.mesure, SUM(pd.quantite) AS quantite,d.departement FROM produits_demandes pd
             INNER JOIN demandes d ON d.id=pd.demande INNER JOIN produits p ON p.id=pd.produit
             WHERE d.departement=$departement AND d.status = 'livrée' GROUP BY p.id,p.nom,p.code,p.mesure,d.departement"
        ));
        return response()->json(['inventaire' => $inventaire]);
    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
