<?php
namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Caisse\Encaissement;
use Illuminate\Http\Request;

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
        $encaissements = Encaissement::with('attributionLinked', 'produits', 'plats')->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function insert(Request $request)
    {
        $encaissement = new Encaissement($request->all());
        $encaissement->impayer();
        $encaissement->save();
        foreach ($request->boissons as $article) {
            $encaissement->produits()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
        }
        foreach ($request->plats as $article) {
            $encaissement->plats()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
        }
        $message = "La caisse a enregistrée avec succès la consommation, code: $encaissement->nom";
        $encaissement = Encaissement::with('plats', 'produits')->find($encaissement->id);
        return response()->json([
            'message' => $message,
            'encaissement' => [
                'id' => $encaissement->id,
                'nom' => $encaissement->nom,
                'status' => $encaissement->status,
                'created_at' => $encaissement->created_at,
                'code' => $encaissement->code,
                'produits' => $encaissement->produits,
                'plats' => $encaissement->plats,
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $encaissement = Encaissement::with('attributionLinked', 'produits', 'plats')->find($id);
        return response()->json(['encaissement' => $encaissement]);
    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
