<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Reservation;
use Carbon\Carbon;
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
            $reservation->terminer();
            $reservation->save();
        }
        $chambre->occuper();
        $chambre->save();
        $message = "La chambre $chambre->nom a été attribuée avec succès";
        return response()->json(['message' => $message]);
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

    public function update(int $id, Request $request)
    {
        $this->validate($request, Attribution::RULES);
        $attribution = Attribution::with('chambreLinked')->find($id);
        $attribution->entree = $request->entree;
        $attribution->sortie = $request->sortie;
        $attribution->accompagnants = $request->accompagnants;
        $attribution->destination = $request->destination;
        $attribution->client = $request->client;
        $attribution->chambre = $request->chambre;
        $attribution->remise = $request->remise;
        $attribution->save();
        $message = "L'hébergement concernant la chambre " . $attribution->chambreLinked->nom . " a été modifié avec succès";
        return response()->json(['message' => $message]);
    }

    public function liberer(int $id)
    {
        $attribution = Attribution::find($id);
        $attribution->liberer();
        $attribution->date_liberation = Carbon::now();
        $attribution->save();
        $chambre = Chambre::find($attribution->chambre);
        $chambre->liberer();
        $chambre->save();
        $message = 'la chambre ' . $chambre->nom . ' a été libérée.';
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $attribution = Attribution::find($id);
        $attribution->delete();
        $chambre = Chambre::find($attribution->chambre);
        $chambre->liberer();
        $message = "l'hébergement, chambre $chambre->nom a été supprimée.";
        return response()->json(['message' => $message]);
    }
}
