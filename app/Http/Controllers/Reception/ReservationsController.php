<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationsController extends Controller
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
        $reservations = Reservation::with(['clientLinked', 'chambreLinked.prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->get();
        return response()->json(['reservations' => $reservations]);
    }

    public function getEvents()
    {
        $reservations = Reservation::with('clientLinked', 'chambreLinked', 'attribution')->reserved()->get();
        $attributions = Attribution::with('clientLinked', 'chambreLinked')->isBusy()->get();
        $events = array_merge($reservations->all(), $attributions->all());
        return response()->json(['events' => $events]);
    }

    public function getReserved()
    {
        $reservations = Reservation::reserved()->with('clientLinked', 'chambreLinked')->get();
        return response()->json(['reservations' => $reservations]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Reservation::RULES);
        $reservation = new Reservation($request->all());
        empty($request->accompagnants) ? $reservation->accompagnants = 0 : null;
        $chambre = Chambre::find($request->chambre);
        $reservation->prix = $chambre->prix_vente;
        $reservation->genererCode();
        $reservation->reserver();
        $reservation->save();
        $message = "La chambre $chambre->nom a été attribuée avec succès";
        return response()->json(['message' => $message]);
    }

    public function getOne(int $id)
    {
        $reservation = Reservation::with(['chambreLinked', 'clientLinked.pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'encaissement.reservationLinked', 'encaissement.versements.mobile'])->find($id);
        return response()->json(['reservation' => $reservation]);
    }

    public function update(int $id, Request $request)
    {
        $reservation = Reservation::find($id);
        $reservation->entree = $request->entree;
        $reservation->sortie = $request->sortie;
        $reservation->save();
        $message = "La reservation $reservation->code a été modifiée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function annuler(int $id)
    {
        $reservation = Reservation::with('chambreLinked')->find($id);
        $reservation->annuler();
        $reservation->date_annulation = Carbon::now();
        $reservation->save();
        $message = 'la réservation ' . $reservation->chambreLinked->nom . ' a été annulée.';
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $reservation = Reservation::with('chambreLinked', 'attribution')->find($id);
        $reservation->delete();
        if (!empty($reservation->attribution)) {
            $attribution = Attribution::where('reservation', $reservation->id)->first();
            $attribution->delete();
        }
        $message = 'la réservation de la chambre ' . $reservation->chambreLinked->nom . ' a été supprimée';
        return response()->json(['message' => $message]);
    }

    public function utilisees()
    {
        $attributions = Attribution::with('chambreLinked', 'clientLinked')->isBusy()->get();
        $reservations = Reservation::with('chambreLinked', 'clientLinked')->reserved()->get();
        $hebergements = array_merge($reservations->all(), $attributions->all());
        return response()->json(['hebergements' => $hebergements]);
    }
}
