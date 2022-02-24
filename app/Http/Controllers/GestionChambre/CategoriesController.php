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
        $categories = CategorieChambre::select('id', 'nom', 'description')->get();
        return response()->json(['categories' => $categories]);
    }

    public function getCategories()
    {
        $datas = CategorieChambre::with('chambres')->get();
        $categories = array_map(function ($categorie) {
            return [
                'id' => $categorie->id,
                'nom' => $categorie->nom,
                'max' => $categorie->chambres->max('prix_vente') ?? 0,
                'min' => $categorie->chambres->min('prix_vente') ?? 0,
            ];
        }, $datas->all());
        $categories = array_filter($categories, function ($categorie) {
            return $categorie['max'] > 0 and $categorie['min'] > 0;
        });
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
