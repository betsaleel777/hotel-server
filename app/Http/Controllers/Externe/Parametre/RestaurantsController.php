<?php

namespace App\Http\Controllers\Externe\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Externe\Parametre\Restaurant;
use Illuminate\Http\Request;

class RestaurantsController extends Controller
{
    public function getAll()
    {
        $restaurants = Restaurant::get();
        return response()->json(['restaurants' => $restaurants]);
    }

    public function trashed()
    {
        $restaurants = Restaurant::onlyTrashed()->get();
        return response()->json(['restaurants' => $restaurants]);
    }

    public function getOne(int $id)
    {
        $restaurant = Restaurant::find($id);
        return response()->json(['restaurant' => $restaurant]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Restaurant::RULES);
        $restaurant = new Restaurant($request->all());
        $restaurant->save();
        $message = "le restaurant $restaurant->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Restaurant::regle($id));
        $restaurant = Restaurant::find($id);
        $restaurant->fill($request->all());
        $restaurant->save();
        $message = "le restaurant $restaurant->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $restaurant = Restaurant::find($id);
        $restaurant->delete();
        $message = "le restaurant $restaurant->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function suppression(array $restaurants)
    {
        $message = null;
        count($restaurants) > 1 ? $message = self::massDelete($restaurants) : $message = self::delete($restaurants[0]);
        return response()->json(['message' => $message]);
    }

    public function restauration(array $restaurants)
    {
        $message = null;
        count($restaurants) > 1 ? $message = self::massRestore($restaurants) : $message = self::restore($restaurants[0]);
        return response()->json(['message' => $message]);
    }

    private static function restore(int $id): String
    {
        $restaurant = Restaurant::withTrashed()->find($id);
        $restaurant->restore();
        return "le restaurant $restaurant->nom a été restauré avec succès.";
    }

    private static function massRestore(array $ids): String
    {
        $restaurants = Restaurant::withTrashed()->whereIn('id', $ids)->get();
        foreach ($restaurants as $restaurant) {
            $restaurant->restore();
        }
        return "Liste restaurée avec succès.";
    }

    private static function delete(int $id): String
    {
        $restaurant = Restaurant::withTrashed()->find($id);
        $restaurant->forceDelete();
        return "le restaurant $restaurant->nom a été archivé avec succès.";
    }

    private static function massDelete(array $ids): String
    {
        $restaurants = Restaurant::withTrashed()->whereIn('id', $ids)->get();
        foreach ($restaurants as $restaurant) {
            $restaurant->forceDelete();
        }
        return "Liste restaurée avec succès.";
    }
}
