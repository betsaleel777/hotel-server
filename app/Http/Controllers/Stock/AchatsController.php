<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Achat;
use Illuminate\Http\Request;

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
        $achats = Achat::with('produit')->get();
        return response()->json(['achats' => $achats]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Achat::RULES);
        $achat = new achat($request->all());
        $achat->genererCode();
        $achat->save();
        $message = "l' achat $achat->code a  été crée avec succès.";
        $achat = Achat::with('produit')->find($achat->id);
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

    public function update(int $id, Request $request)
    {
        $this->validate($request, Achat::regles($id));
        $achat = Achat::find($id);
        $achat->nom = $request->nom;
        $achat->mesure = $request->mesure;
        $achat->mode = $request->mode;
        $achat->type = $request->type;
        $achat->seuil = $request->seuil;
        $achat->save();
        $message = "l' achat $achat->code a  été modifié avec succès.";
        $achat = Achat::with('produit')->find($achat->id);
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

    public function delete(int $id)
    {
        $achat = Achat::find($id);
        $achat->delete();
        $message = "l' achat $achat->code a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'achat' => ['id' => $achat->id, 'code' => $achat->code]]);
    }

    public function quantiteStock(int $id)
    {
        $achats = Achat::where('ingredient', $id)->get();
        $quantite = $achats->sum('quantite');
        return response()->json(['quantite' => $quantite]);
    }
}
