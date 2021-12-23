<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Entretien;
use App\Models\Maintenance\Note;
use Illuminate\Http\Request;

class EntretiensController extends Controller
{
    public function getAll()
    {
        $entretiens = Entretien::with('employe', 'chambre', 'note')->get();
        return response()->json(['entretiens' => $entretiens]);
    }

    public function getTrashed()
    {
        $entretiens = Entretien::with('employe', 'chambre')->onlyTrashed()->get();
        return response()->json(['entretiens' => $entretiens]);
    }

    public function getOne(int $id)
    {
        $entretien = Entretien::with('employe', 'chambre', 'note')->find($id);
        return response()->json(['entretien' => $entretien]);
    }

    public function getByRoom(int $room)
    {
        $entretiens = Entretien::with('employe', 'chambre', 'note')->where('chambre_id', $room)->has('note')->orderBy('entree', 'DESC')->get();
        return response()->json(['entretiens' => $entretiens]);
    }

    public function insert(Request $request)
    {
        foreach ($request->dates as $date) {
            $entretien = new Entretien($request->all());
            $entretien->genererCode();
            $entretien->entree = $date['entree'];
            $entretien->sortie = $date['sortie'];
            $entretien->save();
        }
        $message = "Tout les entretiens de la liste sont été enregistrés avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        if ($request->has('description')) {
            $this->validate($request, Note::RULES);
            $entretien = Entretien::find($id);
            $entretien->description = $request->description;
            $entretien->status = Entretien::TERMINER;
            $entretien->save();
            $note = new Note($request->all());
            $note->entretien_id = $id;
            $note->save();
            $message = "L'entretien a été noté et commenté avec succès.";
        } else {
            $this->validate($request, Entretien::RULES);
            $entretien = Entretien::find($id);
            $entretien->fill($request->all());
            $entretien->save();
            $message = "L'entretien a été modifié avec succès.";
        }
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $entretien = Entretien::withTrashed()->find($id);
        $entretien->forceDelete();
        $message = "L'entretien $entretien->code a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $entretien = Entretien::withTrashed()->find($id);
        $entretien->restore();
        $message = "L'entretien $entretien->code a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $entretien = Entretien::find($id);
        $entretien->delete();
        $message = "L'entretien $entretien->code a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
