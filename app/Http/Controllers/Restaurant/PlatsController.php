<?php
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant\Plat;
use App\Models\Restaurant\Prix;
use App\Models\Stock\Achat;
use App\Models\Stock\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PlatsController extends Controller
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
        $plats = Plat::with(['prix' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'categorieLinked', 'produits'])->get();
        return response()->json(['plats' => $plats]);
    }

    public function insert(Request $request)
    {
        //création du plat
        $rules = array_merge(Plat::RULES, Prix::RULES);
        $this->validate($request, $rules);
        $plat = new Plat($request->except('image'));
        $plat->genererCode();
        $plat->save();
        //creation prix
        $prix = new Prix(['achat' => $request->achat, 'vente' => $request->vente]);
        $prix->plat = $plat->id;
        $prix->save();
        //creation ingrédients
        foreach ($request->ingredients as $ingredient) {
            $plat->produits()->attach($ingredient['id'], ['quantite' => $ingredient['quantite'], 'commentaire' => $ingredient['commentaire']]);
        }
        $message = "le Plat $plat->code a été crée avec succès.";
        $plat = Plat::with(['prix' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'categorieLinked', 'produits'])->find($plat->id);
        return response()->json([
            'message' => $message,
            'plat' => [
                'id' => $plat->id,
                'code' => $plat->code,
                'nom' => $plat->nom,
                'categorie' => $plat->categorie,
                'categorieNom' => $plat->categorieLinked->nom,
                'image' => $plat->image,
                'produits' => $plat->produits,
                'description' => $plat->description,
                'achat' => $plat->prix[0]->achat,
                'vente' => $plat->prix[0]->vente,
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $plat = Plat::find($id);
        return response()->json(['plat' => $plat]);
    }

    public function update(int $id, Request $request)
    {
        //modification du plat
        $rules = array_merge(Plat::regles($id), Prix::RULES);
        $this->validate($request, $rules);
        $plat = Plat::find($id);
        $plat->nom = $request->nom;
        $plat->categorie = $request->categorie;
        $plat->description = $request->description;
        $plat->save();
        //création nouveau prix
        $prix = new Prix(['achat' => $request->achat, 'vente' => $request->vente]);
        $prix->plat = $plat->id;
        $dirtyAchat = $prix->isDirty('achat');
        $dirtyVente = $prix->isDirty('vente');
        if ($dirtyAchat or $dirtyVente) {
            $prix->save();
        }

        //modification ingrédients
        $toSync = [];
        foreach ($request->ingredients as $ingredient) {
            $toSync[$ingredient['id']] = ['quantite' => $ingredient['quantite'], 'commentaire' => $ingredient['commentaire']];
        }
        //return response()->json($toSync, 400);
        $plat->produits()->sync($toSync);
        $message = "Le plat $plat->code a  été modifié avec succès.";
        $plat = Plat::with(['prix' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'categorieLinked', 'produits'])->find($plat->id);
        return response()->json([
            'message' => $message,
            'plat' => [
                'id' => $plat->id,
                'code' => $plat->code,
                'nom' => $plat->nom,
                'categorie' => $plat->categorie,
                'categorieNom' => $plat->categorieLinked->nom,
                'image' => $plat->image,
                'produits' => $plat->produits,
                'description' => $plat->description,
                'achat' => $plat->prix[0]->achat,
                'vente' => $plat->prix[0]->vente,
            ],
        ]);

    }

    public function delete(int $id)
    {
        $plat = Plat::find($id);
        $plat->delete();
        $message = "le plat $plat->code a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'plat' => ['id' => $plat->id, 'code' => $plat->code]]);
    }

    public function prixMinimal(Request $request)
    {
        $ingredients = new Collection($request->ingredients);
        $ids = array_column($request->ingredients, 'id');

        // vérification de l'existance d'un approvisionnement pour chaque ingredient de la liste qui compose le plat
        foreach ($ids as $id) {
            $ingredient = Produit::select('nom')->find($id);
            $achats = Achat::where('ingredient', $id)->first();
            if (empty($achats)) {
                $message = "l'ingrédient $ingredient->nom n'a jamais été approvisionné.";
                return response()->json(['message' => $message], 400);
            }
        }

        $prixAchat = 0;
        foreach ($ids as $id) {
            $achats = Achat::where('ingredient', $id)->orderBy('id', 'DESC')->limit(10)->get();
            if (!empty($achats)) {
                $ingredient = $ingredients->where('id', $id)->first();
                $prixAchat += ($ingredient['quantite'] * $achats->sum('prix_achat')) / $achats->sum('quantite');
            }
        }
        $message = "prix de revient du plat estimé à partir de la liste des ingrédients et du stock.";
        return response()->json(['achat' => $prixAchat, 'message' => $message]);
    }
}
