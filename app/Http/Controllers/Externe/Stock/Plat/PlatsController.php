<?php

namespace App\Http\Controllers\Externe\Stock\Plat;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Plat\Plat;
use App\Models\Externe\Stock\Plat\Prix;
use Illuminate\Http\Request;

class PlatsController extends Controller
{
    public function getAll()
    {
        $plats = Plat::with('categorie', 'articles')->get();
        return response()->json(['plats' => $plats]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $plats = Plat::onlyTrashed()->with('categorie')->where('restaurant_id', $restaurant)->get();
        return response()->json(['plats' => $plats]);
    }

    public function getFromRestau(int $restaurant)
    {
        $plats = Plat::with('categorie', 'articles')->where('restaurant_id', $restaurant)->get();
        return response()->json(['plats' => $plats]);
    }

    public function getOne(int $id)
    {
        $plat = Plat::with('categorie', 'articles')->find($id);
        return response()->json(['plat' => $plat]);
    }

    public function insert(Request $request)
    {
        $rules = array_merge(Plat::RULES, Prix::RULES);
        $this->validate($request, $rules);
        $plat = new Plat($request->except('articles'));
        $plat->genererCode();
        $plat->save();
        $prix = new Prix(['montant' => $request->prix_vente, 'plat_id' => $plat->id, 'restaurant_id' => $request->restaurant_id]);
        $prix->save();
        foreach ($request->articles as $article) {
            $plat->articles()->attach($article['id'], ['quantite' => $article['quantite']]);
        }
        $message = "le plat $plat->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $rules = $rules = array_merge(Plat::regles($id), Prix::RULES);
        $this->validate($request, $rules);
        $plat = Plat::find($id);
        $plat->fill($request->except('articles'));
        if ($plat->isDirty('prix_vente')) {
            $prix = new Prix(['montant' => $request->prix_vente, 'plat_id' => $id, 'restaurant_id' => $request->restaurant_id]);
            $prix->save();
        }
        $plat->save();
        $toSync = [];
        foreach ($request->articles as $article) {
            $toSync[$article['id']] = ['quantite' => $article['quantite']];
        }
        $plat->articles()->sync($toSync);
        $message = "le plat $plat->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Plat::withTrashed()->find($id);
        $article->restore();
        $message = "le plat $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $plat = Plat::find($id);
        $plat->delete();
        $message = "le plat $plat->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $plat = Plat::withTrashed()->find($id);
        $plat->forceDelete();
        $message = "le plat $plat->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }
}
