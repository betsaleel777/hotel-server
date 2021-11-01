<?php

namespace App\Http\Controllers\Externe\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Externe\Parametre\Mobile;
use Illuminate\Http\Request;

class MobilesController extends Controller
{
    public function getAll()
    {
        $mobiles = Mobile::get();
        return response()->json(['moyens' => $mobiles]);
    }

    public function getFromRestau(int $restaurant)
    {
        $mobiles = Mobile::where('restaurant_id', $restaurant)->get();
        return response()->json(['moyens' => $mobiles]);
    }

    public function trashed()
    {
        $mobiles = Mobile::onlyTrashed()->get();
        return response()->json(['moyens' => $mobiles]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $mobiles = Mobile::onlyTrashed()->where('restaurant_id', $restaurant)->get();
        return response()->json(['moyens' => $mobiles]);
    }

    public function getOne(int $id)
    {
        $mobile = Mobile::find($id);
        return response()->json(['moyen' => $mobile]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Mobile::RULES);
        $mobile = new Mobile($request->all());
        $mobile->save();
        $message = "le paiement mobile $mobile->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Mobile::regle($id));
        $mobile = Mobile::find($id);
        $mobile->fill($request->all());
        $mobile->save();
        $message = "le paiement mobile $mobile->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $mobile = Mobile::find($id);
        $mobile->delete();
        $message = "le paiement mobile $mobile->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Mobile::withTrashed()->find($id);
        $article->restore();
        $message = "le paiement mobile $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $article = Mobile::withTrashed()->find($id);
        $article->forceDelete();
        $message = "la paiement mobile $article->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }
}
