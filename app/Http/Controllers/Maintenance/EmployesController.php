<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Employe;
use Illuminate\Http\Request;

class EmployesController extends Controller
{
    public function getAll()
    {
        $employes = Employe::get();
        return response()->json(['employes' => $employes]);
    }

    public function getTrashed()
    {
        $employes = Employe::onlyTrashed()->get();
        return response()->json(['employes' => $employes]);
    }

    public function getOne(int $id)
    {
        $employe = Employe::find($id);
        return response()->json(['employé' => $employe]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Employe::RULES);
        $employe = new Employe($request->all());
        $employe->genererCode();
        $employe->save();
        $message = "l'employé $employe->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        $this->validate($request, Employe::regle($id));
        $employe = Employe::find($id);
        $employe->fill($request->all());
        $employe->save();
        $message = "l'employé $employe->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $employe = Employe::withTrashed()->find($id);
        $employe->restore();
        $message = "l'employé $employe->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $employe = Employe::withTrashed()->find($id);
        $employe->forceDelete();
        $message = "l'employé $employe->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $employe = Employe::find($id);
        $employe->delete();
        $message = "l'employé $employe->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
