<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Categorie;
use Illuminate\Http\Request;

class CategoriesController extends Controller
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
        $categories = Categorie::select('id', 'nom')->get();
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
        $message = "La categorie de plat, $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message, 'id' => $categorie->id]);
    }

    public function getOne(int $id)
    {

    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Categorie::regles($id));
        $categorie = Categorie::find($id);
        $categorie->nom = $request->nom;
        $categorie->save();
        $message = "La categorie de chambre $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $categorie = Categorie::withTrashed()->find($id);
        $categorie->forceDelete();
        $message = "la catégorie de chambre $categorie->nom a été supprimée avec succès";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $categorie = Categorie::withTrashed()->find($id);
        $categorie->restore();
        $message = "la catégorie $categorie->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $categorie = Categorie::find($id);
        $categorie->delete();
        $message = "la catégorie $categorie->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
