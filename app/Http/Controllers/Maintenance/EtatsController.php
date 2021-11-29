<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Etat;
use Illuminate\Http\Request;

class EtatsController extends Controller
{
    public function getAll()
    {
        $fournitures = Etat::get();
        return response()->json(['fournitures' => $fournitures]);
    }

    public function getTrashed()
    {
        $fournitures = Etat::onlyTrashed()->get();
        return response()->json(['fournitures' => $fournitures]);
    }

    public function getOne(int $id)
    {
        $fourniture = Etat::find($id);
        return response()->json(['fourniture' => $fourniture]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Etat::RULES);
        $fourniture = new Etat($request->all());
        $fourniture->save();
        $message = "la fourniture $fourniture->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        $this->validate($request, Etat::regle($id));
        $fourniture = Etat::find($id);
        $fourniture->fill($request->all());
        $fourniture->save();
        $message = "la fourniture $fourniture->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $fourniture = Etat::withTrashed()->find($id);
        $fourniture->restore();
        $message = "la fourniture $fourniture->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $fourniture = Etat::withTrashed()->find($id);
        $fourniture->forceDelete();
        $message = "la fourniture $fourniture->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $fourniture = Etat::find($id);
        $fourniture->delete();
        $message = "la fourniture $fourniture->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
