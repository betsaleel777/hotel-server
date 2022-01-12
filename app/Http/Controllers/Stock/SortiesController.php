<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Demande;
use App\Models\Stock\Sortie;
use Illuminate\Http\Request;

class SortiesController extends Controller
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
        $sorties = Sortie::with('produits', 'departementLinked', 'demandeLinked')->get();
        return response()->json(['sorties' => $sorties]);
    }

    public function insertFromDemande(Request $request)
    {
        $this->validate($request, Sortie::RULES);
        $demande = Demande::find($request->demande);
        $sortie = new Sortie($request->all(), $demande->titre);
        $sortie->save();
        foreach ($request->articles as $article) {
            $sortie->produits()->attach($article['id'], ['quantite' => (int) $article['valeur'], 'demandees' => (int) $article['quantite']]);
        }
        $message = "La sortie, $sortie->titre a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function confirm(int $id, Request $request)
    {
        $demande = Demande::find($id);
        $sortie = Sortie::where('demande', $id)->first();
        foreach ($request->articles as $article) {
            $sortie->produits()->updateExistingPivot($article['id'], ['recues' => $article['valeur']]);
        }
        $demande->confirmer();
        $demande->save();
        $message = "La reception de la demande a été enregistrée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Sortie::RULES);
        $sortie = new Sortie($request->all());
        $sortie->titre = $request->titre;
        $sortie->save();
        foreach ($request->articles as $article) {
            $sortie->produits()->attach($article['produit'], ['quantite' => (int) $article['quantite'], 'demandees' => 0]);
        }
        $message = "La sortie, $sortie->titre a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function getFromDemande(int $demande)
    {
        $sortie = Sortie::with('produits', 'departementLinked', 'demandeLinked')->where('demande', $demande)->first();
        return response()->json(['sortie' => $sortie]);
    }

    public function getOne(int $id)
    {

    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
