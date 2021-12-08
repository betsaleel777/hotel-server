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
        $chambre->prix_vente = $request->montant;
        $chambre->liberer();
        $chambre->genererCode();
        $chambre->save();
        //enregistrement dans la table des prix de chambre
        $prix = new PrixChambre(['montant' => $request->montant]);
        $prix->chambre = $chambre->id;
        $prix->save();

        $message = "La chambre $chambre->nom a été crée avec succes.";
        return response()->json(['message' => $message]);
    }

    public function insertState(Request $request)
    {
        $chambre = Chambre::find($request->id);
        foreach ($request->equipements as $equipement) {
            $chambre->equipements()->attach($equipement['id'], [
                'quantite' => $equipement['quantite'],
                'libelle' => $equipement['libelle'],
            ]);
        }
        $message = "L'état de la chambre $chambre->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function getOne(int $id)
    {
        $chambre = Chambre::with(['prixList' => function ($query) {
            return $query->orderBy('created_at', 'ASC');}, 'categorieLinked', 'equipements'])->find($id);
        return response()->json(['chambre' => $chambre]);
    }

    public function update(int $id, Request $request)
    {
        $rules = array_merge(PrixChambre::RULES, Chambre::regles($id));
        $this->validate($request, $rules);

        $chambre = Chambre::find($id);
        $chambre->nom = $request->nom;
        $chambre->prix_vente = $request->montant;
        $chambre->categorie = $request->categorie;
        $chambre->save();

        $prix = new PrixChambre(['montant' => $request->montant]);
        $prix->chambre = $chambre->id;
        $dirtyPrix = $prix->isDirty('montant');
        if ($dirtyPrix) {
            $prix->save();
        }
        $message = "La chambre a été modifiée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function updateState(int $id, Request $request)
    {
        $chambre = Chambre::find($request->id);
        $toSync = [];
        foreach ($request->equipements as $equipement) {
            $toSync[$equipement['id']] = ['quantite' => $equipement['quantite'], 'libelle' => $equipement['libelle']];
        }
        $chambre->equipements()->sync($toSync);
        $message = "L'état de la chambre $chambre->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $chambre = Chambre::find($id);
        $chambre->delete();
        $message = "La chambre $chambre->nom a été supprimée avec succès.";
        return response()->json(['message' => $message]);
    }
}
