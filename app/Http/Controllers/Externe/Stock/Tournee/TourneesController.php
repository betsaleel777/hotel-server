<?php

namespace App\Http\Controllers\Externe\Stock\Tournee;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Tournee\Prix;
use App\Models\Externe\Stock\Tournee\Tournee;
use Illuminate\Http\Request;

class TourneesController extends Controller
{
    public function getAll()
    {
        $tournees = Tournee::with('categorie')->get();
        return response()->json(['tournees' => $tournees]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $tournees = Tournee::onlyTrashed()->with('categorie')->where('restaurant_id', $restaurant)->get();
        return response()->json(['tournees' => $tournees]);
    }

    public function getFromRestau(int $restaurant)
    {
        $tournees = Tournee::with('categorie')->where('restaurant_id', $restaurant)->get();
        return response()->json(['tournees' => $tournees]);
    }

    public function getOne(int $id)
    {
        $tournee = Tournee::with('categorie')->find($id);
        return response()->json(['tournee' => $tournee]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Tournee::RULES);
        $tournee = new Tournee($request->all());
        $tournee->genererCode();
        $tournee->save();
        $prix = new Prix(['montant' => $request->prix_vente, 'tournee_id' => $tournee->id, 'restaurant_id' => $request->restaurant_id]);
        $prix->save();
        $message = "la tournée $tournee->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Tournee::regles($id));
        $tournee = Tournee::find($id);
        $tournee->fill($request->all());
        if ($tournee->isDirty('prix_vente')) {
            $prix = new Prix(['montant' => $request->prix_vente, 'tournee_id' => $id, 'restaurant_id' => $request->restaurant_id]);
            $prix->save();
        }
        $tournee->update($request->all());
        $message = "la tournée $tournee->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Tournee::withTrashed()->find($id);
        $article->restore();
        $message = "la tournée $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $tournee = Tournee::find($id);
        $tournee->delete();
        $message = "la tournée $tournee->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $tournee = Tournee::withTrashed()->find($id);
        $tournee->forceDelete();
        $message = "la tournée $tournee->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }
}
