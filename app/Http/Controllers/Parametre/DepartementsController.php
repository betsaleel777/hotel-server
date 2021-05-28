<?php
namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Departement;
use Illuminate\Http\Request;

class DepartementsController extends Controller
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
        $departements = Departement::select('id', 'nom')->get();
        return response()->json(['departements' => $departements]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Departement::RULES);
        $departement = Departement::create($request->all());
        $message = "Le departement $departement->nom a été crée avec succes.";
        return response()->json([
            'message' => $message,
            'departement' => ['id' => $departement->id, 'nom' => $departement->nom],
        ]);
    }

    public function getOne(int $id)
    {

    }

    public function getByName(string $name)
    {
        $departement = Departement::where('nom', $name)->first();
        return response()->json(['departement' => $departement]);
    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
