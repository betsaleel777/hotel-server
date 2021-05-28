<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Demande;
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

    private static function checkDemande(array $articles)
    {
        $ids = array_column($articles, 'id');
        $badProduct = null;
        $achats = DB::select(DB::Raw(
            "SELECT IFNULL(AVG(a.prix_achat),0)  AS prix,IFNULL(SUM(a.quantite),0) AS quantite,p.id,p.nom
             FROM motel.approvisionements AS a RIGHT JOIN motel.produits
             AS p ON p.id=a.ingredient  WHERE p.id IN (" . implode(',', $ids) . ") GROUP BY a.ingredient,p.id,p.nom"
        ));
        foreach ($articles as $article) {
            $casser = true;
            foreach ($achats as $achat) {
                if ($article['id'] === $achat->id) {
                    $casser = (int) $article['pivot']['quantite'] > (int) $achat->quantite;
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

    public function cloner(request $request)
    {
        $demande = new Demande($request->all());
        $demande->precedant = $request->id;
        $titre = explode('--', $demande->titre);
        if (isset($titre[1])) {
            $number = $titre[1] + 1;
            $demande->titre = $titre[0] . '--' . $number;
        } else {
            $demande->titre = $request->titre . '--1';
        }
        $demande->save();
        foreach ($request->produits as $article) {
            $demande->produits()->attach($article['id'], ['quantite' => $article['pivot']['quantite']]);
        }
        $demandeOld = Demande::with('departementLinked', 'produits')->find($demande->precedant);
        $demandeOld->relancer();
        $demandeOld->save();
        $message = "La demande: $request->code, a bien été relancée à partir de la demande: $demande->code.";
        $demande = Demande::with('departementlinked', 'produits')->find($demande->id);
        $demandeOld = Demande::with('departementlinked', 'produits')->find($demande->precedant);
        return response()->json([
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
            'old' => [
                'id' => $demandeOld->id,
                'titre' => $demandeOld->titre,
                'status' => $demandeOld->status,
                'code' => $demandeOld->code,
                'produits' => $demandeOld->produits,
                'created_at' => $demandeOld->created_at,
                'departement' => $demandeOld->departementLinked->id,
            ],
        ]);

    }

    public function getOne(int $id)
    {

    }

    public function accept(int $id, Request $request)
    {
        $demande = Demande::find($id);
        $badProduct = self::checkDemande($request->articles);
        if (empty($badProduct)) {
            $toSync = [];
            foreach ($request->articles as $article) {
                $toSync[$article['id']] = ['quantite' => $article['pivot']['quantite']];
            }
            $demande->produits()->sync($toSync);
            $demande->accepter();
            $demande->save();
            $message = "La demande, $demande->code a été acceptée.";
        } else {
            $message = "La quantité du produit $badProduct a dépassé le stock disponible.";
            return response()->json(['message' => $message], 400);
        }
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

    public function deliver(int $id)
    {
        $demande = Demande::find($id);
        $demande->livrer();
        $demande->save();
        $message = "La demande: $demande->code a été livrée avec succès.";
        return response()->json(self::returning($id, $message));
    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
