<?php
namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Caisse\MobileMoney;
use Illuminate\Http\Request;

class MobileMoneyController extends Controller
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
        $mobilesMoney = MobileMoney::get();
        return response()->json(['mobiles' => $mobilesMoney]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, MobileMoney::RULES);
        MobileMoney::create($request->all());
        $message = "Le moyen de paiement mobile, $request->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function getOne(int $id)
    {
        $mobilesMoney = MobileMoney::find($id);
        return response()->json(['moyen' => $mobilesMoney]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, MobileMoney::regles($id));
        $mobilesMoney = MobileMoney::find($id);
        $mobilesMoney->nom = $request->nom;
        $mobilesMoney->save();
        $message = "Moyen de paiement mobile, modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $mobilesMoney = MobileMoney::find($id);
        $mobilesMoney->delete();
        $message = "Le moyen de paiement $mobilesMoney->nom a été supprimé avec succès.";
        return response()->json(['message' => $message]);
    }
}
