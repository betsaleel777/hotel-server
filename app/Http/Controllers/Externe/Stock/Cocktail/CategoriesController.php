<?php

namespace App\Http\Controllers\Externe\Stock\Cocktail;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Cocktail\Categorie;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function getAll()
    {
        $categories = Categorie::get();
        return response()->json(['categories' => $categories]);
    }

    public function getFromRestau(int $restaurant)
    {
        $categories = Categorie::where('restaurant_id', $restaurant)->get();
        return response()->json(['categories' => $categories]);
    }

    public function trashed()
    {
        $categories = Categorie::onlyTrashed()->get();
        return response()->json(['categories' => $categories]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $categories = Categorie::onlyTrashed()->where('restaurant_id', $restaurant)->get();
        return response()->json(['categories' => $categories]);
    }

    public function getOne(int $id)
    {
        $categorie = Categorie::find($id);
        return response()->json(['categorie' => $categorie]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Categorie::RULES);
        $categorie = new Categorie($request->all());
        $categorie->save();
        $message = "la catégorie $categorie->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Categorie::regles($id));
        $categorie = Categorie::find($id);
        $categorie->fill($request->except('restaurant_id'));
        $categorie->save();
        $message = "la categorie $categorie->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Categorie::withTrashed()->find($id);
        $article->restore();
        $message = "la catégorie $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $cocktail = Categorie::find($id);
        $cocktail->delete();
        $message = "la catégorie $cocktail->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $cocktail = Categorie::withTrashed()->find($id);
        $cocktail->forceDelete();
        $message = "la catégorie $cocktail->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }
}
