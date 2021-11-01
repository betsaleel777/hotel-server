<?php

namespace App\Http\Controllers\Externe\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Externe\Caisse\Facture;
use App\Models\Externe\Caisse\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaiementsController extends Controller
{
    public function getAll()
    {
        $paiements = Paiement::with('moyen')->get();
        return response()->json(['paiements' => $paiements]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Paiement::RULES);
        $facture = Facture::find($request->facture_id);
        if ((int) $request->dejaVerse < (int) $request->montantApayer) {
            $facture->impayer();
        } else {
            $facture->payer();
            $facture->date_soldee = Carbon::now();
        }
        $Paiement = new Paiement($request->except('dejaVerse', 'montantApayer'));
        $Paiement->save();
        $facture->save();
        return response()->json([
            'message' => "Le paiement de la facture $facture->code a été enregistré avec succès.",
        ]);
    }
}
