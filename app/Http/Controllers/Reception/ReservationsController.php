<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Reservation;
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
        $reservations = Reservation::with('clientLinked', 'chambreLinked', 'attribution')->used()->get();
        $attributions = Attribution::doesntHave('reservationLinked')->with('clientLinked', 'chambreLinked')->get();
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
        $reservation->genererCode();
        $reservation->reserver();
        $reservation->save();
        $chambre = Chambre::find($request->chambre);
        $message = "La chambre $chambre->nom a été attribuée avec succès, code: $reservation->code";
        $reservation = Reservation::with('chambreLinked', 'clientLinked')->find($reservation->id);
        return response()->json([
            'message' => $message,
            'reservation' => [
                'id' => $reservation->id,
                'code' => $reservation->code,
                'status' => $reservation->status,
                'chambre' => ['id' => $reservation->chambreLinked->id, 'nom' => $reservation->chambreLinked->nom],
                'client' => ['id' => $reservation->clientLinked->id, 'nom' => $reservation->clientLinked->nom],
                'entree' => $reservation->entree,
                'sortie' => $reservation->sortie,
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $reservation = Reservation::find($id);
        return response()->json(['reservation' => $reservation]);
    }

    public function update(Request $request)
    {

    }

    public function annuler(int $id)
    {
        $reservation = Reservation::with('chambreLinked')->find($id);
        $reservation->annuler();
        $reservation->save();
        $message = 'la réservation ' . $reservation->chambreLinked->nom . ' a été annulée.';
        return response()->json([
            'message' => $message,
            'reservation' => ['id' => $reservation->id, 'code' => $reservation->code],
        ]);
    }

    public function delete(int $id)
    {
        $reservation = Reservation::with('chambreLinked', 'attribution')->find($id);
        $reservation->delete();
        if (!empty($reservation->attribution)) {
            $attribution = Attribution::where('reservation', $reservation->id)->first();
            $attribution->delete();
        }
        $message = 'la réservation ' . $reservation->code . ' de la chambre ' . $reservation->chambreLinked->nom . ' a été supprimée';
        return response()->json(['message' => $message, 'reservation' => ['id' => $reservation->id, 'code' => $reservation->code]]);
    }
}
