<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Reservation;
use Illuminate\Http\Request;

class AttributionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getAll()
    {
        $attributions = Attribution::with(['clientLinked', 'chambreLinked.prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->get();
        return response()->json(['attributions' => $attributions]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Attribution::RULES);
        $attribution = new Attribution($request->all());
        $chambre = Chambre::find($request->chambre);
        $attribution->prix = $chambre->prix_vente;
        $attribution->occuper();
        $attribution->genererCode();
        $attribution->save();
        if ($request->filled('reservation')) {
            $reservation = Reservation::find($request->reservation);
            $reservation->occuper();
            $reservation->save();
        }
        $chambre->occuper();
        $chambre->save();
        $message = "La chambre $chambre->nom a été attribuée avec succès, code: $attribution->code";
        $attribution = Attribution::with('chambreLinked', 'clientLinked')->find($attribution->id);
        return response()->json([
            'message' => $message,
            'attribution' => [
                'id' => $attribution->id,
                'code' => $attribution->code,
                'status' => $attribution->status,
                'chambre' => ['id' => $attribution->chambreLinked->id, 'nom' => $attribution->chambreLinked->nom],
                'client' => ['id' => $attribution->clientLinked->id, 'nom' => $attribution->clientLinked->nom],
                'entree' => $attribution->entree,
                'sortie' => $attribution->sortie,
            ]]);
    }

    public function getOne(int $id)
    {
        $attribution = Attribution::with(['chambreLinked', 'clientLinked.pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        },
            'consommation.produits', 'consommation.plats', 'consommation.cocktails',
            'consommation.tournees', 'encaissement.reservationLinked', 'encaissement.versements.mobile'])->find($id);
        return response()->json(['attribution' => $attribution]);
    }

    public function update(Request $request)
    {

    }

    public function liberer(int $id)
    {
        $attribution = Attribution::find($id);
        $attribution->liberer();
        $attribution->save();
        $chambre = Chambre::find($attribution->chambre);
        $chambre->liberer();
        $chambre->save();
        $message = 'la chambre ' . $chambre->nom . ' a été libérée.';
        return response()->json([
            'message' => $message,
            'attribution' => [
                'id' => $attribution->id,
                'code' => $attribution->code,
            ],
        ]);
    }

    public function delete(int $id)
    {
        $attribution = Attribution::find($id);
        $attribution->delete();
        $chambre = Chambre::find($attribution->chambre);
        $chambre->liberer();
        $message = "la réception $attribution->code pour la chambre $chambre->nom a été supprimée.";
        return response()->json([
            'message' => $message,
            'attribution' => ['id' => $attribution->id, 'code' => $attribution->code],
        ]);
    }
}
