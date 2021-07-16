<?php
namespace App\Http\Controllers\Bar;

use App\Http\Controllers\Controller;
use App\Models\Bar\Prix;
use App\Models\Bar\Tournee;
use App\Models\Stock\Produit;
use Illuminate\Http\Request;

class TourneesController extends Controller
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
        $tournees = Tournee::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'produitLinked'])->get();
        return response()->json(['tournees' => $tournees]);
    }

    public function insert(Request $request)
    {
        $rules = array_merge(Prix::RULES, Tournee::RULES);
        $this->validate($request, $rules);
        $tournee = new Tournee($request->all());
        $produit = Produit::find($request->produit);
        $tournee->genererCode();
        empty($tournee->titre) ? $tournee->titre = $produit->nom . ' tournée' : $tournee->titre = $request->titre;
        $tournee->prix_vente = $request->montant;
        $tournee->save();
        // enregistrement dans la table des prix de chambre
        $prix = new Prix(['montant' => $request->montant, 'tournee' => $tournee->id]);
        $prix->save();

        $tournee = Tournee::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'produitLinked'])->find($tournee->id);
        $message = "La Tournee $tournee->code ($tournee->titre) a été crée avec succes.";
        return response()->json([
            'message' => $message,
            'tournee' => [
                'id' => $tournee->id,
                'code' => $tournee->code,
                'titre' => $tournee->titre,
                'nombre' => $tournee->nombre,
                'contenance' => $tournee->contenance,
                'produit' => $tournee->produit,
                'montant' => $tournee->prixList[0]->montant,
            ],
        ]);
    }

    public function getOne(int $id)
    {

    }

    public function update(int $id, Request $request)
    {
        $rules = array_merge(Prix::RULES, Tournee::regles($id));
        $this->validate($request, $rules);

        $tournee = Tournee::find($id);
        $tournee->titre = $request->titre;
        $tournee->nombre = $request->nombre;
        $tournee->contenance = $request->contenance;
        $tournee->prix_vente = $request->montant;
        $tournee->save();

        $prix = new Prix(['montant' => $request->montant, 'tournee' => $tournee->id]);
        $prix->save();
        $dirtyPrix = $prix->isDirty('montant');
        if ($dirtyPrix) {
            $prix->save();
        }

        $tournee = Tournee::with(['prixList' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'produitLinked'])->find($tournee->id);
        $message = "La Tournee $tournee->code ($tournee->titre) a été modifiée avec succes.";
        return response()->json([
            'message' => $message,
            'tournee' => [
                'id' => $tournee->id,
                'code' => $tournee->code,
                'titre' => $tournee->titre,
                'nombre' => $tournee->nombre,
                'contenance' => $tournee->contenance,
                'produit' => $tournee->produit,
                'montant' => $tournee->prixList[0]->montant,
            ],
        ]);
    }

    public function delete(int $id)
    {
        $tournee = Tournee::find($id);
        $tournee->delete();
        $message = "La tournee $tournee->code a été supprimée avec succès.";
        return response()->json([
            'message' => $message,
            'tournee' => ['code' => $tournee->code, 'id' => $tournee->id],
        ]);
    }
}
