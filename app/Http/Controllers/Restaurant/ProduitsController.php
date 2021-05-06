<?php
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Reception\Client;
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
        $produit = new Produit($request->all());
        $produit->genererCode();
        $produit->save();
        $message = "le produit $produit->code a  été crée avec succès.";
        $produit = Client::find($produit->id);
        return response()->json([
            'message' => $message,
            'client' => [
                'id' => $produit->id,
                'code' => $produit->code,
                'nom' => $produit->nom,
                'image' => $produit->image,
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

    }

    public function delete(int $id)
    {
        $produit = Produit::find($id);
        $produit->delete();
        $message = "le produit $produit->code a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'produit' => ['id' => $produit->id, 'code' => $produit->code]]);
    }
}
