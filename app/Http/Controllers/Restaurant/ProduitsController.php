<?php
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant\Produit;
use Illuminate\Http\Request;

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

    public function getAll()
    {
        $produits = Produit::get();
        return response()->json(['produits' => $produits]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Produit::RULES);
        $produit = new Produit($request->except('image'));
        $produit->genererCode();
        $produit->save();
        $message = "le produit $produit->code a  été crée avec succès.";
        $produit = Produit::find($produit->id);
        return response()->json([
            'message' => $message,
            'produit' => [
                'id' => $produit->id,
                'code' => $produit->code,
                'nom' => $produit->nom,
                'mesure' => $produit->mesure,
                'image' => [],
                'mode' => $produit->mode,
                'type' => $produit->type,
                'seuil' => $produit->seuil,
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $produit = Produit::find($id);
        return response()->json(['produit' => $produit]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Produit::regles($id));
        $produit = Produit::find($id);
        $produit->nom = $request->nom;
        $produit->mesure = $request->mesure;
        $produit->mode = $request->mode;
        $produit->type = $request->type;
        $produit->seuil = $request->seuil;
        $produit->save();
        $message = "le produit $produit->code a  été modifié avec succès.";
        $produit = Produit::find($produit->id);
        return response()->json([
            'message' => $message,
            'produit' => [
                'id' => $produit->id,
                'code' => $produit->code,
                'nom' => $produit->nom,
                'mesure' => $produit->mesure,
                'image' => [],
                'mode' => $produit->mode,
                'type' => $produit->type,
                'seuil' => $produit->seuil,
            ],
        ]);
    }

    public function delete(int $id)
    {
        $produit = Produit::find($id);
        $produit->delete();
        $message = "le produit $produit->code a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'produit' => ['id' => $produit->id, 'code' => $produit->code]]);
    }
}
