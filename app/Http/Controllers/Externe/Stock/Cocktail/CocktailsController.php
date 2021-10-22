<?php

namespace App\Http\Controllers\Externe\Stock\Cocktail;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Cocktail\Cocktail;
use App\Models\Externe\Stock\Cocktail\Prix;
use Illuminate\Http\Request;

class CocktailsController extends Controller
{
    public function getAll()
    {
        $cocktails = Cocktail::with('categorie', 'tournees')->get();
        return response()->json(['cocktails' => $cocktails]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $cocktails = Cocktail::onlyTrashed()->with('categorie')->where('restaurant_id', $restaurant)->get();
        return response()->json(['cocktails' => $cocktails]);
    }

    public function getFromRestau(int $restaurant)
    {
        $cocktails = Cocktail::with('categorie', 'tournees')->where('restaurant_id', $restaurant)->get();
        return response()->json(['cocktails' => $cocktails]);
    }

    public function getOne(int $id)
    {
        $cocktail = Cocktail::with('categorie', 'tournees')->find($id);
        return response()->json(['cocktail' => $cocktail]);
    }

    public function insert(Request $request)
    {
        $rules = array_merge(Cocktail::RULES, Prix::RULES);
        $this->validate($request, $rules);
        $cocktail = new Cocktail($request->except('melanges'));
        $cocktail->genererCode();
        $cocktail->save();
        $prix = new Prix(['montant' => $request->prix_vente, 'cocktail_id' => $cocktail->id, 'restaurant_id' => $request->restaurant_id]);
        $prix->save();
        //création des ingrédients (les tournées mélangées ensemble pour former le cocktail)
        foreach ($request->melanges as $melange) {
            $cocktail->tournees()->attach($melange['id'], ['quantite' => $melange['quantite']]);
        }
        $message = "le cocktail $cocktail->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $rules = $rules = array_merge(Cocktail::regles($id), Prix::RULES);
        $this->validate($request, $rules);
        $cocktail = Cocktail::find($id);
        $cocktail->fill($request->except('melanges'));
        if ($cocktail->isDirty('prix_vente')) {
            $prix = new Prix(['montant' => $request->prix_vente, 'cocktail_id' => $id, 'restaurant_id' => $request->restaurant_id]);
            $prix->save();
        }
        $cocktail->save();
        //modification des ingrédients (les tournées mélangées ensemble pour former le cocktail)
        $toSync = [];
        foreach ($request->melanges as $melange) {
            $toSync[$melange['id']] = ['quantite' => $melange['quantite']];
        }
        $cocktail->tournees()->sync($toSync);
        $message = "Le cocktail $cocktail->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Cocktail::withTrashed()->find($id);
        $article->restore();
        $message = "le cocktail $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $cocktail = Cocktail::find($id);
        $cocktail->delete();
        $message = "le cocktail $cocktail->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $cocktail = Cocktail::withTrashed()->find($id);
        $cocktail->forceDelete();
        $message = "le cocktail $cocktail->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }
}
