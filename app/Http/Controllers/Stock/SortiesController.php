<?php
namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Demande;
use App\Models\Stock\Sortie;
use Illuminate\Http\Request;

class SortiesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    private static function returning(int $id, string $message)
    {
        $sortie = Sortie::with('demandelinked', 'produits')->find($id);
        return [
            'message' => $message,
            'demande' => [
                'id' => $sortie->id,
                'titre' => $sortie->titre,
                'status' => $sortie->status,
                'code' => $sortie->code,
                'produits' => $sortie->produits,
                'created_at' => $sortie->created_at,
                'demande' => $sortie->demandeLinked->id,
            ],
        ];
    }

    public function getAll()
    {
        $sorties = Sortie::with('produits', 'demandeLinked')->get();
        return response()->json(['sorties' => $sorties]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Sortie::RULES);
        $demande = Demande::find($request->demande);
        $sortie = new Sortie($request->all(), $demande->titre);
        $sortie->save();
        foreach ($request->articles as $article) {
            $sortie->produits()->attach($article['id'], ['quantite' => (int) $article['valeur'], 'demandees' => (int) $article['quantite']]);
        }
        $message = "La sortie, $sortie->code a été crée avec succes.";
        return response()->json(self::returning($sortie->id, $message));
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