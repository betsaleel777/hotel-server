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

    public function insert(Request $request)
    {
        $this->validate($request, Categorie::RULES);
        $categorie = Categorie::create($request->all());
        $message = "La categorie de plat, $categorie->nom a été crée avec succes.";
        return response()->json([
            'message' => $message,
            'categorie' => ['id' => $categorie->id, 'nom' => $categorie->nom],
        ]);
    }

    public function getOne(int $id)
    {

    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
