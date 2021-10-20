<?php

namespace App\Http\Controllers\Externe\Stock\Tournee;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Tournee\Categorie;
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
        $message = "la categorie $categorie->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }
}
