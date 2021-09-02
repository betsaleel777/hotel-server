<?php
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant\Categorie;
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

    public function insert(Request $request)
    {
        $this->validate($request, Categorie::RULES);
        $categorie = Categorie::create($request->all());
        $message = "La categorie de plat, $categorie->nom a été crée avec succes.";
        return response()->json(['message' => $message]);
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
        $categorie = Categorie::find($id);
        $categorie->delete();
        $message = "la catégorie de chambre $categorie->nom a été supprimée avec succès";
        return response()->json(['message' => $message]);
    }
}
