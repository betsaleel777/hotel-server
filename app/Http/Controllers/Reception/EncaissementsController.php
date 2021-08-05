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

    public static function setReservationStatus($encaissement, Request $request): void
    {
        $reservation = Reservation::with(['chambreLinked.prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->find($request->reservation);
        if ((int) $request->dejaVerse < (int) $request->montantApayer) {
            $encaissement->en_cours();
        } else {
            $encaissement->solder();
        }
        $encaissement->save();
    }

    public static function setAttributionStatus($encaissement, $request): void
    {
        $attribution = Attribution::with(['chambreLinked.prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->find($request->attribution);
        if ((int) $request->dejaVerse < (int) $request->montantApayer) {
            $encaissement->en_cours();
        } else {
            $encaissement->solder();
        }
        $encaissement->save();
    }

    public function getAll()
    {
        $encaissements = Encaissement::with([
            'attributionLinked.clientLinked', 'attributionLinked.chambreLinked',
            'reservationLinked.clientLinked', 'reservationLinked.chambreLinked',
            'anterieur', 'versements.mobile'])->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function insert(Request $request)
    {
        if (isset($request->reservation)) {
            $encaissement = Encaissement::where('reservation', $request->reservation)->first();
            if (empty($encaissement)) {
                $encaissement = new Encaissement($request->all());
            }
            self::setReservationStatus($encaissement, $request);
            $versement = new Versement($request->all());
            $versement->encaissement = $encaissement->id;
            $versement->save();
            $message = "La caisse de la réception a enregistrée le versement avec succès, code: $encaissement->code pour la somme de $versement->montant FCFA";
            $encaissement = Encaissement::with(['versements' => function ($query) {
                return $query->orderBy('id', 'DESC');
            }, 'versements.mobile'])->find($encaissement->id);
            return response()->json([
                'message' => $message,
                'versement' => $encaissement->versements[0],
                'status' => $encaissement->status,
            ]);
        } else {
            $encaissement = Encaissement::where('attribution', $request->attribution)->first();
            if (empty($encaissement)) {
                $encaissement = new Encaissement($request->all());
            }
            self::setAttributionStatus($encaissement, $request);
            $versement = new Versement($request->all());
            $versement->encaissement = $encaissement->id;
            $versement->save();
            $message = "La caisse de la réception a enregistrée le versement avec succès, code: $encaissement->code pour la somme de $versement->montant FCFA";
            $encaissement = Encaissement::with(['versements' => function ($query) {
                return $query->orderBy('id', 'DESC');
            }, 'versements.mobile'])->find($encaissement->id);
            return response()->json([
                'message' => $message,
                'versement' => $encaissement->versements[0],
                'status' => $encaissement->status,
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
