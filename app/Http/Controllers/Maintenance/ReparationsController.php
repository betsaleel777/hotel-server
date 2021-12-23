<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\OrdresReparation;
use App\Models\Maintenance\Reparation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReparationsController extends Controller
{
    public function getAll()
    {
        $reparations = Reparation::with('categorie', 'chambre', 'ordres')->get();
        return response()->json(['reparations' => $reparations]);
    }

    public function getTrashed()
    {
        $reparations = OrdresReparation::with('provider', 'reparation.chambre', 'reparation.categorie')->onlyTrashed()->get();
        return response()->json(['reparations' => $reparations]);
    }

    public function getOrdres()
    {
        $ordres = OrdresReparation::with('provider', 'reparation.chambre', 'reparation.categorie')->get();
        return response()->json(['ordres' => $ordres]);
    }

    public function getByRoom(int $room)
    {
        $reparations = Reparation::with(['ordres' => function ($query) {
            $query->orderBy('entree', 'DESC');
        }, 'ordres.provider', 'chambre', 'categorie'])->where('chambre_id', $room)->get()->toArray();
        $ordres = [];
        foreach ($reparations as $reparation) {
            $ordres = array_merge($ordres, $reparation['ordres']);
        };
        return response()->json(['ordres' => $ordres]);
    }

    public function getIncompletes()
    {
        $ordres = OrdresReparation::with(['reparation' => function ($q) {
            $q->incompleted();
        }, 'reparation.chambre', 'reparation.categorie', 'provider'])->get();
        return response()->json(['ordres' => $ordres]);
    }

    public function getOne(int $id)
    {
        $reparation = Reparation::with('chambre', 'categorie', 'ordres.provider')->find($id);
        return response()->json(['reparation' => $reparation]);
    }

    public function getByDate(string $date)
    {
        $format = Carbon::parse($date)->format('Y-m-d');
        $reparations = Reparation::with('chambre', 'categorie', 'ordres')->whereDate('created_at', $format)->get();
        return response()->json(['reparations' => $reparations]);
    }

    public function insert(Request $request)
    {
        if (empty($request->incomplete)) {
            $rules = array_merge(Reparation::RULES, OrdresReparation::RULES);
            $this->validate($request, $rules);
            $reparation = new Reparation($request->all());
            $reparation->genererCode();
            $reparation->save();
            $ordre = new OrdresReparation($request->all());
            $ordre->genererCode();
            $ordre->reparation_id = $reparation->id;
            $ordre->save();
        } else {
            $this->validate($request, OrdresReparation::RULES);
            $ordre = new OrdresReparation($request->all());
            $ordre->genererCode();
            $ordre->reparation_id = $request->incomplete;
            $ordre->save();
        }
        $message = "La réparation a été enregistrée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        $rules = array_merge(Reparation::regle($request->reparation_id), OrdresReparation::EDIT_RULES);
        $this->validate($request, $rules);
        $reparation = Reparation::find($request->reparation_id);
        $reparation->fill($request->all());
        $reparation->save();
        $ordre = OrdresReparation::find($id);
        $ordre->fill($request->all());
        $ordre->save();
        $message = "La réparation $reparation->code a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $reparation = Reparation::withTrashed()->find($id);
        $reparation->forceDelete();
        $message = "La réparation $reparation->code a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $reparation = Reparation::withTrashed()->find($id);
        $reparation->restore();
        $message = "La réparation $reparation->code a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $reparation = Reparation::find($id);
        $reparation->delete();
        $message = "La réparation $reparation->code a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
