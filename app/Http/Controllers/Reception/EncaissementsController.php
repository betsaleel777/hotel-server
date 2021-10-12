<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Caisse\Encaissement as CaisseEncaissement;
use App\Models\Reception\Attribution;
use App\Models\Reception\Encaissement;
use App\Models\Reception\Reservation;
use App\Models\Reception\Versement;
use Carbon\Carbon;
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
        if ((int) $request->dejaVerse < (int) $request->montantApayer) {
            $encaissement->en_cours();
        } else {
            $encaissement->solder();
            $encaissement->date_soldee = Carbon::now();
        }
        $encaissement->save();
    }

    public static function setAttributionStatus($encaissement, $request): void
    {
        if ((int) $request->dejaVerse < (int) $request->montantApayer) {
            $encaissement->en_cours();
        } else {
            $encaissement->solder();
            $encaissement->date_soldee = Carbon::now();
            $attribution = Attribution::with('consommation')->find($encaissement->attribution);
            $consommation = $attribution->consommation ? CaisseEncaissement::find($attribution->consommation->id) : null;
            if (!empty($consommation)) {
                $consommation->date_soldee = Carbon::now();
                $consommation->payer();
                $consommation->save();
            }

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
        $this->validate($request, Versement::RULES);
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

    public function getSoldes()
    {
        $encaissements = Encaissement::with([
            'attributionLinked.clientLinked', 'attributionLinked.chambreLinked',
            'attributionLinked.consommation.produits', 'attributionLinked.consommation.plats', 'attributionLinked.consommation.cocktails',
            'attributionLinked.consommation.tournees', 'reservationLinked.clientLinked', 'reservationLinked.chambreLinked',
            'anterieur', 'versements.mobile'])->soldes()->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function getNonSoldes()
    {
        $encaissements = Encaissement::with([
            'attributionLinked.clientLinked', 'attributionLinked.chambreLinked',
            'attributionLinked.consommation.produits', 'attributionLinked.consommation.plats', 'attributionLinked.consommation.cocktails',
            'attributionLinked.consommation.tournees', 'reservationLinked.clientLinked', 'reservationLinked.chambreLinked',
            'anterieur', 'versements.mobile'])->nonSoldes()->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function getByDate(string $date)
    {
        $format = Carbon::parse($date)->format('Y-m-d');
        $encaissements = Encaissement::with([
            'attributionLinked.clientLinked', 'attributionLinked.chambreLinked',
            'reservationLinked.clientLinked', 'reservationLinked.chambreLinked',
            'anterieur', 'versements.mobile'])->whereDate('date_soldee', $format)->get();
        return response()->json(['encaissements' => $encaissements]);
    }

    public function update(Request $request)
    {
        return response()->json($request->all());
    }

    public function delete()
    {

    }
}
