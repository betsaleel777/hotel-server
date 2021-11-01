<?php

namespace App\Http\Controllers\Externe\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Externe\Caisse\Table;
use Illuminate\Http\Request;

class TablesController extends Controller
{
    public function getAll()
    {
        $tables = Table::get();
        return response()->json(['tables' => $tables]);
    }

    public function getFromRestau(int $restaurant)
    {
        $tables = Table::where('restaurant_id', $restaurant)->get();
        return response()->json(['tables' => $tables]);
    }

    public function trashed()
    {
        $tables = Table::onlyTrashed()->get();
        return response()->json(['tables' => $tables]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $tables = Table::onlyTrashed()->where('restaurant_id', $restaurant)->get();
        return response()->json(['tables' => $tables]);
    }

    public function getOne(int $id)
    {
        $table = Table::find($id);
        return response()->json(['table' => $table]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Table::RULES);
        $table = new table($request->all());
        $table->save();
        $message = "la table $table->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Table::regle($id));
        $table = Table::find($id);
        $table->fill($request->all());
        $table->save();
        $message = "la table $table->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $table = Table::find($id);
        $table->delete();
        $message = "la table $table->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Table::withTrashed()->find($id);
        $article->restore();
        $message = "la table $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $article = Table::withTrashed()->find($id);
        $article->forceDelete();
        $message = "la table $article->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

}
