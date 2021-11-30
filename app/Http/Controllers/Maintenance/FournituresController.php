<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Fourniture;
use Illuminate\Http\Request;

class FournituresController extends Controller
{
    public function getAll()
    {
        $fournitures = Fourniture::get();
        return response()->json(['fournitures' => $fournitures]);
    }

    public function getTrashed()
    {
        $fournitures = Fourniture::onlyTrashed()->get();
        return response()->json(['fournitures' => $fournitures]);
    }

    public function getOne(int $id)
    {
        $fourniture = Fourniture::find($id);
        return response()->json(['fourniture' => $fourniture]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Fourniture::RULES);
        $fourniture = new Fourniture($request->all());
        $fourniture->genererCode();
        $fourniture->save();
        $message = "la fourniture $fourniture->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        $this->validate($request, Fourniture::regle($id));
        $fourniture = Fourniture::find($id);
        $fourniture->fill($request->all());
        $fourniture->save();
        $message = "la fourniture $fourniture->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $fourniture = Fourniture::withTrashed()->find($id);
        $fourniture->forceDelete();
        $message = "la fourniture $fourniture->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $fourniture = Fourniture::withTrashed()->find($id);
        $fourniture->restore();
        $message = "la fourniture $fourniture->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $fourniture = Fourniture::find($id);
        $fourniture->delete();
        $message = "la fourniture $fourniture->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
