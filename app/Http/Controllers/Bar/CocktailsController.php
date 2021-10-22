<?php
namespace App\Http\Controllers\Bar;

use App\Http\Controllers\Controller;
use App\Models\Bar\Cocktail;
use App\Models\Bar\PrixCocktail;
use Illuminate\Http\Request;

class CocktailsController extends Controller
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
        $cocktails = Cocktail::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'tournees'])->get();
        return response()->json(['cocktails' => $cocktails]);
    }

    public function insert(Request $request)
    {
        //création du plat
        $rules = array_merge(Cocktail::RULES, PrixCocktail::RULES);
        $this->validate($request, $rules);
        $cocktail = new Cocktail($request->all());
        $cocktail->prix_vente = $request->montant;
        $cocktail->genererCode();
        $cocktail->save();
        //creation prix
        $prix = new PrixCocktail(['montant' => $request->montant]);
        $prix->cocktail = $cocktail->id;
        $prix->save();
        //creation ingrédients
        foreach ($request->ingredients as $ingredient) {
            $cocktail->tournees()->attach($ingredient['id'], ['quantite' => $ingredient['quantite']]);
        }
        $message = "Le cocktail $cocktail->nom a été crée avec succès.";
        return response()->json(['message' => $message]);

    }

    public function getOne(int $id)
    {

    }

    public function update(int $id, Request $request)
    {
        $rules = array_merge(Cocktail::regles($id), PrixCocktail::RULES);
        $this->validate($request, $rules);
        $cocktail = Cocktail::find($id);
        $cocktail->nom = $request->nom;
        $cocktail->description = $request->description;
        $cocktail->prix_vente = $request->montant;
        $cocktail->save();

        $prix = new PrixCocktail(['montant' => $request->montant, 'cocktail' => $cocktail->id]);
        if ($prix->isDirty('montant')) {
            $prix->save();
        }

        //modification des ingrédients
        $toSync = [];
        foreach ($request->ingredients as $ingredient) {
            $toSync[$ingredient['id']] = ['quantite' => $ingredient['quantite']];
        }
        $cocktail->tournees()->sync($toSync);
        $message = "Le cocktail $cocktail->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $cocktail = Cocktail::find($id);
        $cocktail->delete();
        $message = "Le cocktail $cocktail->nom, nommé $ a été supprimée avec succès.";
        return response()->json(['message' => $message]);
    }
}
