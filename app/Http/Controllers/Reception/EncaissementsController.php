<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Reception\Attribution;
use App\Models\Reception\Encaissement;
use App\Models\Reception\Reservation;
use App\Models\Reception\Versement;
use Illuminate\Http\Request;

class EncaissementsController extends Controller
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
        $encaissements = Encaissement::with([
            'attributionLinked.clientLinked', 'attributionLinked.chambreLinked',
            'reservationLinked.clientLinked', 'reservationLinked.chambreLinked',
            'anterieur', 'versements'])->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function insert(Request $request)
    {
        // $request->mode === 'reserve' ? $rules = array_merge(Versement::RULES, ['reservation' => 'required']) : $rules = array_merge(Versement::RULES, ['attribution' => 'required']);
        //$this->validate($request, $rules);
        if (isset($request->reservation)) {
            $encaissement = Encaissement::where('reservation', $request->reservation)->first();
            if (empty($encaissement)) {
                $encaissement = new Encaissement($request->all());
                $reservation = Reservation::with(['chambreLinked.prixList' => function ($query) {
                    return $query->orderBy('id', 'DESC');
                }])->find($request->reservation);
                if ((int) $reservation->chambreLinked->prixList[0]->montant * $reservation->entree->diffInDays($reservation->sortie) > $request->montant) {
                    $encaissement->en_cours();
                } else {
                    $encaissement->solder();
                }
                $encaissement->save();
            }
            $versement = new Versement($request->all());
            $versement->encaissement = $encaissement->id;
            $versement->save();
            $message = "La caisse de la réception a enregistrée le versement avec succès, code: $encaissement->code pour la somme de $versement->montant FCFA";
            $encaissement = Encaissement::with('versements')->find($encaissement->id);
            return response()->json([
                'message' => $message,
                'versements' => $encaissement->versements,
            ]);

        } else {
            $encaissement = Encaissement::where('attribution', $request->reservation)->first();
            if (empty($encaissement)) {
                $attribution = Attribution::with(['chambreLinked.prixList' => function ($query) {
                    return $query->orderBy('id', 'DESC');
                }])->find($request->attribution);
                if ((int) $attribution->chambreLinked->prixList[0]->montant * $attribution->entree->diffInDays($attribution->sortie) > $request->montant) {
                    $encaissement->en_cours();
                } else {
                    $encaissement->solder();
                }
                $encaissement->save();
            }
            $versement = new Versement($request->all());
            $versement->encaissement = $encaissement->id;
            $versement->save();
            $message = "La caisse de la réception a enregistrée le versement avec succès, code: $encaissement->code pour la somme de $versement->montant FCFA";
            //besoin paiementReception
            $encaissement = Encaissement::with('versements')->find($encaissement->id);
            return response()->json([
                'message' => $message,
                'versements' => $encaissement->versements,
            ]);
        }
    }

    public function getOne(int $id)
    {

    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
