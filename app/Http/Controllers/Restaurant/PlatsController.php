<?php
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant\Achat;
use App\Models\Restaurant\Plat;
use App\Models\Restaurant\Prix;
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
        }, 'categorieLinked'])->get();
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
        }, 'categorieLinked'])->find($plat->id);
        return response()->json([
            'message' => $message,
            'plat' => [
                'id' => $plat->id,
                'code' => $plat->code,
                'nom' => $plat->nom,
                'categorie' => $plat->categorie,
                'categorieNom' => $plat->categorieLinked->nom,
                'image' => $plat->image,
                'description' => $plat->mode,
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
        $this->validate($request, Plat::regles($id));
        $plat = Plat::find($id);
        $plat->nom = $request->nom;
        $plat->categorie = $request->categorie;
        $plat->description = $request->description;
        $plat->save();

        $prix = new Prix(['achat' => $request->achat, 'vente' => $request->vente]);
        $prix->plat = $plat->id;
        $dirtyAchat = $prix->isDirty('achat');
        $dirtyVente = $prix->isDirty('vente');
        if ($dirtyAchat or $dirtyVente) {
            $prix->save();
        }

        $message = "le Plat $plat->code a  été modifié avec succès.";
        $plat = Plat::with(['prix' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }, 'categorieLinked'])->find($plat->id);
        return response()->json([
            'message' => $message,
            'plat' => [
                'id' => $plat->id,
                'code' => $plat->code,
                'nom' => $plat->nom,
                'categorie' => $plat->mesure,
                'categorieNom' => $plat->mesure,
                'image' => $plat->image,
                'description' => $plat->mode,
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
        $prixAchat = 0;
        $prixVente = 0;
        foreach ($ids as $id) {
            $achats = Achat::where('ingredient', $id)->take(10)->get();
            if (!empty($achats)) {
                $prixMoyenAchat = $achats->avg('prix_achat');
                $prixMoyenVente = $achats->avg('prix_vente');
                $ingredient = $ingredients->where('id', $id)->first();
                $prixAchat += $ingredient['quantite'] * $prixMoyenAchat;
                $prixVente += $ingredient['quantite'] * $prixMoyenVente;
            }
        }
        $message = "les prix proposées sont des prix minimaux calculé a partir de la liste des ingrédients.";
        return response()->json(['achat' => $prixAchat, 'vente' => $prixVente, 'message' => $message]);
    }
}
