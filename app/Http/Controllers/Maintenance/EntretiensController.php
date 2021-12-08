<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Entretien;
use Illuminate\Http\Request;

class EntretiensController extends Controller
{
    public function getAll()
    {
        $entretiens = Entretien::with('employe', 'chambre')->get();
        return response()->json(['entretiens' => $entretiens]);
    }

    public function getTrashed()
    {
        $entretiens = Entretien::with('employe', 'chambre')->onlyTrashed()->get();
        return response()->json(['entretiens' => $entretiens]);
    }

    public function getOne(int $id)
    {
        $entretien = Entretien::with('employe', 'chambre')->find($id);
        return response()->json(['entretien' => $entretien]);
    }

    public function insert(Request $request)
    {
        $message = "Les entretiens: ";
        foreach ($request->dates as $date) {
            $entretien = new Entretien($request->all());
            $entretien->genererCode();
            $entretien->entree = $date['entree'];
            $entretien->sortie = $date['sortie'];
            $entretien->save();
            $message .= "$entretien->code, ";
        }
        $message .= "ont été enregistrés avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        $this->validate($request, Entretien::RULES);
        $entretien = Entretien::find($id);
        $entretien->fill($request->all());
        $entretien->save();
        $message = "l'entretien $entretien->code a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $entretien = Entretien::withTrashed()->find($id);
        $entretien->forceDelete();
        $message = "l'entretien $entretien->code a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $entretien = Entretien::withTrashed()->find($id);
        $entretien->restore();
        $message = "l'entretien $entretien->code a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $entretien = Entretien::find($id);
        $entretien->delete();
        $message = "l'entretien $entretien->code a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
