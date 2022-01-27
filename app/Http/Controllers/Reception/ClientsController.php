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
        $this->validate($request, Client::RULES);
        $client = new Client($request->all());
        $client->genererCode();
        $client->customer();
        $client->statut();
        $client->save();
        $piece = new Piece($request->all());
        if (!$piece->dossierVide()) {
            $piece->client = $client->id;
            $piece->save();
        } else {
            $client->status = Client::INCOMPLET;
            $client->save();
        }
        $message = "le client $client->nom a  été crée avec succès.";
        return response()->json(['message' => $message, 'id' => $client->id]);
    }

    public function getOne(int $id)
    {
        $client = Client::with(['pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->find($id);
        return response()->json(['client' => $client]);
    }

    public function getOneByCode(string $code)
    {
        $client = Client::with(['pieces' => function ($query) {
            return $query->orderBy('id', 'DESC');
        }])->where('code', $code)->first();
        return response()->json(['client' => $client]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Client::regles($id));
        $client = Client::find($id);
        $client->fill($request->all());
        $client->save();
        if (isset($request->piece['id'])) {
            $piece = Piece::find($request->piece['id']);
            $piece->fill($request->all());
            $piece->save();
        } else {
            $piece = new Piece($request->all());
            $piece->client = $client->id;
            $piece->save();
        }
        $client->statut();
        $client->save();
        $message = "le client $client->nom a  modifiée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $client = Client::find($id);
        $client->delete();
        $message = "le client $client->nom a été définitivement supprimé avec succès.";
        return response()->json(['message' => $message, 'client' => ['id' => $client->id, 'code' => $client->code]]);
    }
}
