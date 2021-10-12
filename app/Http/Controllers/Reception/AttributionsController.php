<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Client;
use App\Models\Reception\Encaissement;
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
        $attributions = Attribution::with(['chambreLinked', 'clientLinked.pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        },
            'consommation.produits', 'consommation.plats', 'consommation.cocktails',
            'consommation.tournees', 'encaissement.reservationLinked', 'encaissement.versements.mobile'])->get();
        return response()->json(['attributions' => $attributions]);
    }

    public function getBusy()
    {
        $attributions = Attribution::with(['chambreLinked', 'clientLinked.pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        },
            'consommation.produits', 'consommation.plats', 'consommation.cocktails',
            'consommation.tournees', 'encaissement.reservationLinked', 'encaissement.versements.mobile'])->busy()->get();
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
        if (!empty($request->reservation)) {
            $reservation = Reservation::with('encaissement')->find($request->reservation);
            $reservation->terminer();
            $reservation->save();
            if (isset($reservation->encaissement->id)) {
                $encaissement = Encaissement::find($reservation->encaissement->id);
                $encaissement->attribution = $attribution->id;
                $encaissement->save();
            }
        }
        $chambre->occuper();
        $chambre->save();
        $client = Client::select('id', 'nom', 'prenom')->find($attribution->client);
        $message = "La chambre $chambre->nom a été attribuée avec succès au client $client->nom $client->prenom";
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
        $attribution = Attribution::with('chambreLinked', 'clientLinked')->find($id);
        $attribution->entree = $request->entree;
        $attribution->sortie = $request->sortie;
        $attribution->accompagnants = $request->accompagnants;
        $attribution->destination = $request->destination;
        $attribution->client = $request->client;
        $attribution->chambre = $request->chambre;
        $attribution->remise = $request->remise;
        $chambre = Chambre::find($request->chambre);
        $attribution->prix = $chambre->prix_vente;
        $attribution->save();
        $message = "L'hébergement concernant la chambre " . $attribution->chambreLinked->nom . " pour le client " . $attribution->clientLinked->nom . " a été modifié avec succès";
        return response()->json(['message' => $message]);
    }

    public function updateCalendar(int $id, Request $request)
    {
        $attribution = Attribution::with('clientLinked', 'chambreLinked')->find($id);
        $attribution->entree = $request->entree;
        $attribution->sortie = $request->sortie;
        $attribution->save();
        $message = "L'hébergement concernant la chambre " . $attribution->chambreLinked->nom . " pour le client " . $attribution->clientLinked->nom . " a été modifié avec succès";
        return response()->json(['message' => $message]);
    }

    public function liberer(int $id)
    {
        $attribution = Attribution::find($id);
        $attribution->liberer();
        $today = Carbon::now();
        $attribution->date_liberation = $today;
        if ($today->between($attribution->entree, $attribution->sortie)) {
            $attribution->sortie = $today;
        }
        $attribution->save();
        $chambre = Chambre::select('id', 'nom')->find($attribution->chambre);
        $chambre->liberer();
        $chambre->save();
        $client = Client::select('id', 'nom')->find($attribution->client);
        $message = 'la chambre ' . $chambre->nom . ' du client ' . $client->nom . ' a été libérée.';
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $attribution = Attribution::find($id);
        $attribution->delete();
        $chambre = Chambre::find($attribution->chambre);
        $chambre->liberer();
        $chambre->save();
        $message = "l'hébergement, chambre $chambre->nom a été supprimée.";
        return response()->json(['message' => $message]);
    }
}
