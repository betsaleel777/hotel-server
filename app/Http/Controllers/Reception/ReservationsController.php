<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Client;
use App\Models\Reception\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $attributions = Attribution::with('clientLinked', 'chambreLinked')->busyFree()->get();
        $events = array_merge($reservations->all(), $attributions->all());
        return response()->json(['events' => $events]);
    }

    public function getReserved()
    {
        $reservations = Reservation::reserved()->with(['clientLinked', 'encaissement.versements', 'chambreLinked.prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->get();
        return response()->json(['reservations' => $reservations]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Reservation::RULES);
        $reservation = new Reservation($request->all());
        empty($request->accompagnants) ? $reservation->accompagnants = 0 : null;
        $chambre = Chambre::find($request->chambre);
        $reservation->categorie = $chambre->categorie;
        $reservation->prix = $chambre->prix_vente;
        $reservation->genererCode();
        $reservation->reserver();
        $reservation->save();
        $client = Client::find($request->client);
        $message = "La chambre $chambre->nom a été attribuée avec succès au client $client->nom $client->prenom";
        return response()->json(['message' => $message]);
    }

    public function create(Request $request)
    {
        $message = '';
        if (empty($request->client)) {
            $client = new Client();
            $client->nom = $request->nom;
            $client->prenom = $request->prenom;
            $client->email = $request->email;
            $client->prospect();
            $client->genererCode();
            $client->status = Client::INCOMPLET;
            $client->save();
            // $message .= "Un mail a été envoyer sur $request->email.";
        } else {
            $client = Client::where('code', $request->client)->first();
        }
        $reservation = new Reservation();
        $reservation->entree = $request->dates[0];
        $reservation->sortie = $request->dates[1];
        $reservation->client = $client->id;
        $reservation->status = Reservation::PAR_SITE;
        $reservation->categorie = $request->categorie;
        $reservation->genererCode();
        $reservation->save();
        $message .= "Une réservation sans paiement d'accompte ne garantie pas que la chambre vous revienne.\n
                     La réservation du client $request->nom $request->prenom a été prise en compte avec succès.";
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
        $reservation = Reservation::with('chambreLinked', 'clientLinked')->find($id);
        $reservation->entree = $request->entree;
        $reservation->sortie = $request->sortie;
        if ($request->has('accompagnants') and $request->has('destination') and $request->has('chambre')) {
            $reservation->chambre = $request->chambre;
            $chambre = Chambre::find($request->chambre);
            $reservation->destination = $request->destination;
            $reservation->accompagnants = $request->accompagnants;
            $reservation->prix = $chambre->prix_vente;
        }
        if ($reservation->status === Reservation::PAR_SITE) {
            $reservation->status = Reservation::RESERVEE;
            // annulation des réservations qui coincident
            $from = date($reservation->entree);
            $to = date($reservation->sortie);
            DB::table('reservations')->where('status', Reservation::PAR_SITE, true)
                ->where('categorie', $reservation->categorie)->whereBetween('entree', [$from, $to])
                ->orWhereBetween('sortie', [$from, $to])->update(['status' => Reservation::ANNULEE]);
        }
        $reservation->save();
        $message = "La réservation pour le client" . $reservation->chambreLinked->nom . " a été modifiée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function annuler(int $id)
    {
        $reservation = Reservation::with('chambreLinked', 'clientLinked')->find($id);
        $reservation->annuler();
        $reservation->date_annulation = Carbon::now();
        $reservation->save();
        $message = 'la réservation ' . $reservation->chambreLinked->nom . ' pour le client' . $reservation->chambreLinked->nom . ' a été annulée.';
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
        $attributions = Attribution::with('chambreLinked', 'clientLinked')->whereDate('sortie', '>=', date('Y-m-d'))->Busy()->get();
        $reservations = Reservation::with('chambreLinked', 'clientLinked')->whereDate('sortie', '>=', date('Y-m-d'))->reserved()->get();
        $hebergements = array_merge($reservations->all(), $attributions->all());
        return response()->json(['hebergements' => $hebergements]);
    }

    public function utiliseesByCategorie(int $categorie)
    {
        $attributions = DB::table('attributions as a')->select('*')->join('chambres as ch', 'ch.id', '=', 'a.chambre')
            ->join('clients as cl', 'cl.id', '=', 'a.client')->where('ch.categorie', $categorie, true)
            ->where('a.status', Attribution::OCCUPEE, true)->whereDate('a.sortie', '>=', date('Y-m-d'))->get();
        $reservations = DB::table('reservations as r')->select('*')->join('chambres as ch', 'ch.id', '=', 'r.chambre')
            ->join('clients as cl', 'cl.id', '=', 'r.client')->where('ch.categorie', $categorie, true)
            ->where('r.status', Reservation::RESERVEE, true)->whereDate('r.sortie', '>=', date('Y-m-d'))->get();
        $hebergements = array_merge($reservations->all(), $attributions->all());
        return response()->json(['hebergements' => $hebergements]);
    }
}
