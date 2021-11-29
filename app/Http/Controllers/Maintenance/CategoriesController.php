<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Categorie;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function getAll()
    {
        $categories = Categorie::get();
        return response()->json(['categories' => $categories]);
    }

    public function getTrashed()
    {
        $categories = Categorie::onlyTrashed()->get();
        return response()->json(['categories' => $categories]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Categorie::RULES);
        $categorie = Categorie::create($request->all());
        $message = "La categorie de réparation, $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Categorie::regles($id));
        $categorie = Categorie::find($id);
        $categorie->nom = $request->nom;
        $categorie->save();
        $message = "La categorie $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $categorie = Categorie::withTrashed()->find($id);
        $categorie->forceDelete();
        $message = "La catégorie $categorie->nom a été supprimée avec succès";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $categorie = Categorie::withTrashed()->find($id);
        $categorie->restore();
        $message = "La catégorie $categorie->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $categorie = Categorie::find($id);
        $categorie->delete();
        $message = "La catégorie $categorie->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
