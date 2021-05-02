<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Reception\Client;
use App\Models\Reception\Piece;
use Illuminate\Http\Request;

class ClientsController extends Controller
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
        $clients = Client::with(['pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->get();
        return response()->json(['clients' => $clients]);
    }

    public function insert(Request $request)
    {
        $rules = array_merge(Client::RULES, Piece::RULES);
        $this->validate($request, $rules);
        $client = new Client($request->all());
        $client->genererCode();
        $client->save();
        $piece = new Piece($request->all());
        $piece->client = $client->id;
        $piece->save();
        $message = "le client $client->code a  été crée avec succès.";
        $client = Client::with(['pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->find($client->id);
        return response()->json([
            'message' => $message,
            'client' => [
                'id' => $client->id,
                'code' => $client->code,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'pere' => $client->pere,
                'mere' => $client->mere,
                'profession' => $client->profession,
                'email' => $client->email,
                'pays' => $client->pays,
                'domicile' => $client->domicile,
                'contact' => $client->contact,
                'naissance' => $client->naissance,
                'piece' => $client->pieces[0],
            ],
        ]);
    }

    public function getOne(int $id)
    {
        $client = Client::with('pieces')->find($id);
        return response()->json(['client' => $client]);
    }

    public function update(int $id, Request $request)
    {
        $rules = array_merge(Client::regles($id), Piece::regles($request->piece['id']));
        $this->validate($request, $rules);
        $client = Client::find($id);
        $client->fill($request->all());
        $client->save();
        $piece = Piece::find($request->piece['id']);
        $piece->fill($request->all());
        $piece->save();
        $message = "le client $client->code a  modifiée avec succès.";
        $client = Client::with(['pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->find($client->id);
        return response()->json([
            'message' => $message,
            'client' => [
                'id' => $client->id,
                'code' => $client->code,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'pere' => $client->pere,
                'mere' => $client->mere,
                'profession' => $client->profession,
                'email' => $client->email,
                'pays' => $client->pays,
                'domicile' => $client->domicile,
                'contact' => $client->contact,
                'naissance' => $client->naissance,
                'piece' => $client->pieces[0],
            ],
        ]);

    }

    public function delete(int $id)
    {
        $client = Client::find($id);
        $client->delete();
        $message = "le client $client->code a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'client' => ['id' => $client->id, 'code' => $client->code]]);
    }
}
