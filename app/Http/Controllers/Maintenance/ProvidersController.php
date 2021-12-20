<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Maintenance\Provider;
use Illuminate\Http\Request;

class ProvidersController extends Controller
{
    public function getAll()
    {
        $providers = Provider::with('categorie')->get();
        return response()->json(['providers' => $providers]);
    }

    public function getTrashed()
    {
        $providers = Provider::onlyTrashed()->with('categorie')->get();
        return response()->json(['providers' => $providers]);
    }

    public function getOne(int $id)
    {
        $provider = Provider::with('categorie', 'ordres.reparation')->find($id);
        return response()->json(['provider' => $provider]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Provider::RULES);
        $provider = new Provider($request->all());
        $provider->save();
        $message = "l'artisan $provider->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(Request $request, int $id)
    {
        $this->validate($request, Provider::regle($id));
        $provider = Provider::find($id);
        $provider->fill($request->all());
        $provider->save();
        $message = "l'artisan $provider->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $provider = Provider::withTrashed()->find($id);
        $provider->restore();
        $message = "l'artisan $provider->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $provider = Provider::withTrashed()->find($id);
        $provider->forceDelete();
        $message = "l'artisan $provider->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $provider = Provider::find($id);
        $provider->delete();
        $message = "l'artisan $provider->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }
}
