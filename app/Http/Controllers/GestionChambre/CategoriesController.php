<?php
namespace App\Http\Controllers\GestionChambre;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\CategorieChambre;
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
        $categories = CategorieChambre::select('id', 'nom')->get();
        return response()->json(['categories' => $categories]);
    }

    public function getTrashed()
    {
        $categories = CategorieChambre::onlyTrashed()->get();
        return response()->json(['categories' => $categories]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, CategorieChambre::RULES);
        $categorie = CategorieChambre::create($request->all());
        $message = "La categorie de chambre $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message, 'id' => $categorie->id]);
    }

    public function getOne(int $id)
    {

    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, CategorieChambre::regles($id));
        $categorie = CategorieChambre::find($id);
        $categorie->nom = $request->nom;
        $categorie->save();
        $message = "La categorie de chambre $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $categorie = CategorieChambre::withTrashed()->find($id);
        $categorie->forceDelete();
        $message = "la catégorie de chambre $categorie->nom a été supprimée avec succès";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $categorie = CategorieChambre::withTrashed()->find($id);
        $categorie->restore();
        $message = "la catégorie $categorie->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $categorie = CategorieChambre::find($id);
        $categorie->delete();
        $message = "la catégorie $categorie->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
