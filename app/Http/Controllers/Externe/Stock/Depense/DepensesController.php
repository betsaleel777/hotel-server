<?php

namespace App\Http\Controllers\Externe\Stock\Depense;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Depense\Depense;
use Illuminate\Http\Request;

class DepensesController extends Controller
{
    public function getAll()
    {
        $depenses = Depense::with('articles')->get();
        return response()->json(['depenses' => $depenses]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $depenses = Depense::onlyTrashed()->with('articles')->where('restaurant_id', $restaurant)->get();
        return response()->json(['depenses' => $depenses]);
    }

    public function getFromRestau(int $restaurant)
    {
        $depenses = Depense::with('articles')->where('restaurant_id', $restaurant)->get();
        return response()->json(['depenses' => $depenses]);
    }

    public function getFromRestauByDate(int $restaurant, String $date)
    {
        $depenses = Depense::with('articles')->where('restaurant_id', $restaurant)->whereDate('created_at', $date)->get();
        return response()->json(['depenses' => $depenses]);
    }

    public function getTrashedFromRestauByDate(int $restaurant, String $date)
    {
        $depenses = Depense::onlyTrashed()->with('articles')->where('restaurant_id', $restaurant)->whereDate('created_at', $date)->get();
        return response()->json(['depenses' => $depenses]);
    }

    public function getOne(int $id)
    {
        $depense = Depense::with('articles')->find($id);
        return response()->json(['depense' => $depense]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Depense::RULES);
        $depense = new Depense($request->except('pannier'));
        $depense->genererCode();
        $depense->save();
        foreach ($request->pannier as $article) {
            $depense->articles()->attach($article['id'], ['quantite' => $article['quantite'], 'cout' => $article['prix']]);
        }
        $message = "la depense $depense->nom  a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Depense::regles($id));
        $depense = Depense::find($id);
        $depense->fill($request->except('pannier', 'group_date', 'montant', 'jour'));
        $depense->save();
        $toSync = [];
        foreach ($request->pannier as $article) {
            $toSync[$article['id']] = ['quantite' => $article['quantite'], 'cout' => $article['prix']];
        }
        $depense->articles()->sync($toSync);
        $message = "la depense $depense->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Depense::withTrashed()->find($id);
        $article->restore();
        $message = "la depense $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $depense = Depense::find($id);
        $depense->delete();
        $message = "la depense $depense->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $depense = Depense::withTrashed()->find($id);
        $depense->forceDelete();
        $message = "la depense $depense->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }
}
