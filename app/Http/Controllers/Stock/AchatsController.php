<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Restaurant\Ingredient;
use App\Models\Stock\Achat;
use App\Models\Stock\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchatsController extends Controller
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
        $achats = DB::select(DB::Raw(
            "SELECT IFNULL(SUM(a.quantite),0) AS quantite,p.id AS produit,p.nom,p.code,p.mesure
             FROM approvisionements AS a RIGHT JOIN produits AS p ON p.id=a.ingredient
             GROUP BY a.ingredient,p.id,p.nom,p.code,p.mesure ORDER BY quantite DESC"
        ));
        return response()->json(['achats' => $achats]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Achat::RULES);
        $achat = new achat($request->all());
        $achat->genererCode();
        $achat->save();
        $achat = Achat::with('produit')->find($achat->id);
        $message = "l' achat de l'article: $achat->produit->nom a  été crée avec succès.";
        return response()->json([
            'message' => $message,
            'achat' => [
                'id' => $achat->id,
                'code' => $achat->code,
                'quantite' => $achat->quantite,
                'prix_achat' => $achat->prix_achat,
                'prix_vente' => $achat->prix_vente,
                'ingredient' => $achat->ingredient,
                'nom' => $achat->produit->nom,
                'mesure' => $achat->produit->mesure,
                'type' => $achat->produit->type,
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $achat = Achat::find($id);
        return response()->json(['achat' => $achat]);
    }

    public function getFromProduit(int $id)
    {
        $achats = Achat::where('ingredient', $id)->orderBy('id', 'DESC')->get();
        $produit = Produit::find($id);
        return response()->json(['achats' => $achats, 'produit' => $produit]);
    }

    public function update(int $id, Request $request)
    {

    }

    public function delete(int $id)
    {
        $achat = Achat::find($id);
        $achat->delete();
        $achat = Achat::with('produit')->find($achat->id);
        $message = "l'achat $achat->produit->nom a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'achat' => ['id' => $achat->id, 'code' => $achat->code]]);
    }

    public function quantiteStock(int $id)
    {
        $achats = Achat::where('ingredient', $id)->get();
        $quantite = $achats->sum('quantite');
        return response()->json(['quantite' => $quantite]);
    }
}
