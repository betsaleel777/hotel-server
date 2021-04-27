<?php
namespace App\Http\Controllers\GestionChambre;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\GestionChambre\PrixChambre;
use Illuminate\Http\Request;

class ChambresController extends Controller
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
        $chambres = Chambre::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'categorieLinked'])->get();
        return response()->json(['chambres' => $chambres]);
    }

    public function insert(Request $request)
    {
        $rules = array_merge(PrixChambre::RULES, Chambre::RULES);
        $this->validate($request, $rules);
        //creation de la chambre
        $chambre = new Chambre($request->all());
        $chambre->liberer();
        $chambre->genererCode();
        $chambre->save();
        //enregistrement dans la table des prix de chambre
        $prix = new PrixChambre(['montant' => $request->montant]);
        $prix->chambre = $chambre->id;
        $prix->save();

        $chambre = Chambre::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'categorieLinked'])->find($chambre->id);
        $message = "La chambre $chambre->code a été crée avec succes.";
        return response()->json([
            'message' => $message,
            'chambre' => [
                'id' => $chambre->id,
                'code' => $chambre->code,
                'status' => $chambre->status,
                'nom' => $chambre->nom,
                'categorie' => $chambre->categorieLinked->id,
                'standing' => $chambre->categorieLinked->nom,
                'montant' => $chambre->prixList[0]->montant,
            ],
        ]);
    }

    public function getOne(int $id)
    {

    }

    public function update(int $id, Request $request)
    {
        $rules = array_merge(PrixChambre::RULES, Chambre::regles($id));
        $this->validate($request, $rules);

        $chambre = Chambre::find($id);
        $chambre->nom = $request->nom;
        $chambre->categorie = $request->categorie;
        $chambre->save();

        $prix = new PrixChambre(['montant' => $request->montant]);
        $prix->chambre = $chambre->id;
        if ($prix->isDirty('montant')) {
            $prix->save();
        }
        $retour = [];
        if ($chambre->isDirty() or $prix->isDirty('montant')) {
            $chambre = Chambre::with(['prixList' => function ($query) {
                return $query->orderBy('id', 'DESC');
            }, 'categorieLinked'])->find($chambre->id);
            $retour = [
                'id' => $chambre->id,
                'code' => $chambre->code,
                'status' => $chambre->status,
                'nom' => $chambre->nom,
                'categorie' => $chambre->categorieLinked->id,
                'standing' => $chambre->categorieLinked->nom,
                'montant' => $chambre->prixList[0]->montant,
            ];
        }
        $message = "La chambre $chambre->code a été modifiée avec succès.";
        return response()->json(['message' => $message, 'chambre' => $retour]);
    }

    public function delete()
    {

    }
}
