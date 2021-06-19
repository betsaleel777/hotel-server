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
        $encaissements = Encaissement::with('attributionLinked.chambreLinked', 'attributionLinked.clientLinked',
            'produits', 'plats', 'cocktails', 'tournees')->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function getByDepartement(int $id)
    {
        $encaissements = Encaissement::with('attributionLinked.chambreLinked', 'attributionLinked.clientLinked',
            'produits', 'plats', 'cocktails', 'tournees')->where('departement', $id)->get();
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
        foreach ($request->tournees as $article) {
            $encaissement->tournees()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
        }
        foreach ($request->cocktails as $article) {
            $encaissement->cocktails()->attach($article['id'], ['quantite' => $article['valeur'], 'prix_vente' => $article['prix_vente']]);
        }
        $message = "La caisse a enregistrée avec succès la consommation, code: $encaissement->nom";
        $encaissement = Encaissement::with('plats', 'produits', 'cocktails', 'tournees')->find($encaissement->id);
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
                'cocktails' => $encaissement->cocktails,
                'tournees' => $encaissement->tournees,
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $encaissement = Encaissement::with('attributionLinked', 'produits', 'plats', 'cocktails', 'tournees')->find($id);
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
                'cocktails' => $encaissement->cocktails,
                'tournees' => $encaissement->tournees,
            ],
        ]);

    }

    public function delete()
    {

    }
}
