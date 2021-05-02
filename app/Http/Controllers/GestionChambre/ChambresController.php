<?php
namespace App\Http\Controllers\GestionChambre;

use App\Http\Controllers\Controller;
use App\Models\GestionChambre\Chambre;
use App\Models\GestionChambre\PrixChambre;
use App\Models\Reception\Attribution;
use App\Models\Reception\Reservation;
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

    public function getPassage()
    {
        $chambres = Chambre::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->libre()->get();
        return response()->json(['chambres' => $chambres]);
    }

    public function getReservation(string $debut, string $fin)
    {
        $chambresPrises = [];
        //recupérations des ids de chambres prises dans cet intervalle de temps
        //get busy beds
        $attributions = Attribution::with('chambreLinked')->where([['entree', '>=', $debut], ['sortie', '<=', $fin]])->get();
        $reservations = Reservation::with('chambreLinked')->where([['entree', '>=', $debut], ['sortie', '<=', $fin]])->get();
        foreach ($attributions->all() as $attribution) {
            array_push($chambresPrises, $attribution->chambreLinked);
        }
        foreach ($reservations->all() as $reservation) {
            array_push($chambresPrises, $reservation->chambreLinked);
        }
        dd($chambresPrises);
        //get busy bed's ids
        $ids = array_map(function ($chambre) {
            return $chambre->id;
        }, $chambresPrises);
        //on retire les chambres qui ne sont pas dans la liste des ids de chambre prises
        $chambres = Chambre::with('prixList')->get();
        $chambres = array_filter($chambres->all(), function ($chambre) use ($ids) {
            return !in_array($chambre->id, $ids);
        });
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
        $dirtyChambre = $chambre->isDirty();
        $chambre->save();

        $prix = new PrixChambre(['montant' => $request->montant]);
        $prix->chambre = $chambre->id;
        $dirtyPrix = $prix->isDirty('montant');
        if ($dirtyPrix) {
            $prix->save();
        }
        $retour = [];
        if ($dirtyChambre or $dirtyPrix) {
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

    public function delete(int $id)
    {
        $chambre = Chambre::find($id);
        $chambre->delete();
        $message = "La chambre $chambre->code a été supprimée avec succès.";
        return response()->json([
            'message' => $message,
            'chambre' => ['code' => $chambre->code, 'id' => $chambre->id],
        ]);
    }
}
