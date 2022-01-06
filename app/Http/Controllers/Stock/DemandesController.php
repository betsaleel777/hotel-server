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

    public function getAll()
    {
        $demandes = Demande::with('produits', 'departementLinked', 'sortie.produits')->get();
        return response()->json(['demandes' => $demandes]);
    }
    public function getByDepartement(int $departement)
    {
        $demandes = Demande::with('produits', 'departementLinked', 'sortie.produits')->where('departement', $departement)->get();
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
        $message = "La demande, $demande->titre a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function getOne(int $id)
    {
        $demande = Demande::with('produits')->find($id);
        return response()->json(['demande' => $demande]);
    }

    public function getDemandeBar()
    {
        $demandes = Demande::with('produits', 'sortie.produits')->pourBar()->get();
        return response()->json(['demandes' => $demandes]);
    }

    public function getDemandeRestau()
    {
        $demandes = Demande::with('produits', 'sortie.produits')->pourRestaurant()->get();
        return response()->json(['demandes' => $demandes]);
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
            $sortie->produits()->attach($article['id'], ['quantite' => $article['valeur'], 'demandees' => $article['quantite']]);
        }
        $message = "La demande, $demande->titre a été livrée.\n La sortie de stock associée a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function reject(int $id)
    {
        $demande = Demande::find($id);
        $demande->rejetter();
        $demande->save();
        $message = "La demande, $demande->titre a été rejetée.";
        return response()->json(['message' => $message]);
    }

    public function getProductsRestau(int $departement)
    {
        $produits = DB::select(DB::raw(
            "SELECT p.id,p.nom, p.code, p.mesure FROM produits_sorties ps
             INNER JOIN sorties s ON s.id=ps.sortie INNER JOIN produits p ON p.id=ps.produit
             WHERE s.departement=1 GROUP BY ps.produit,p.id,p.nom,p.code,p.mesure"
        ));
        return response()->json(['produits' => $produits]);
    }

    public function getProductsBar(int $departement)
    {
        $produits = DB::select(DB::raw(
            "SELECT p.id,p.nom, p.code, p.mesure FROM produits_sorties ps
             INNER JOIN sorties s ON s.id=ps.sortie INNER JOIN produits p ON p.id=ps.produit
             WHERE s.departement=2 GROUP BY ps.produit,p.id,p.nom,p.code,p.mesure"
        ));
        return response()->json(['produits' => $produits]);
    }
}
