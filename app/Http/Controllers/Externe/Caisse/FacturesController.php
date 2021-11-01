<?php

namespace App\Http\Controllers\Externe\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Externe\Caisse\Facture;
use Illuminate\Http\Request;

class FacturesController extends Controller
{
    public function getAll()
    {
        $factures = Facture::with('articles', 'plats', 'cocktails', 'tournees', 'table')->get();
        return response()->json(['factures' => $factures]);
    }

    public function getNonSoldeesFromRestau(int $restaurant)
    {
        $factures = Facture::with('paiements', 'articles', 'plats', 'cocktails', 'tournees', 'table')
            ->where('restaurant_id', $restaurant)->unpayed()->get();
        return response()->json(['factures' => $factures]);
    }

    public function getSoldeesFromRestau(int $restaurant)
    {
        $factures = Facture::with('paiements', 'articles', 'plats', 'cocktails', 'tournees', 'table')
            ->where('restaurant_id', $restaurant)->payed()->get();
        return response()->json(['factures' => $factures]);
    }

    public function insert(Request $request)
    {
        $facture = new Facture($request->only('description', 'restaurant_id', 'table_id'));
        $facture->genererCode();
        $facture->impayer();
        $facture->save();
        if (isset($request->autres)) {
            foreach ($request->autres as $article) {
                $facture->articles()->attach($article['id'], ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']]);
            }
        }
        if (isset($request->plats)) {
            foreach ($request->plats as $article) {
                $facture->plats()->attach($article['id'], ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']]);
            }
        }
        if (isset($request->tournees)) {
            foreach ($request->tournees as $article) {
                $facture->tournees()->attach($article['id'], ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']]);
            }
        }
        if (isset($request->cocktails)) {
            foreach ($request->cocktails as $article) {
                $facture->cocktails()->attach($article['id'], ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']]);
            }
        }
        $message = "La caisse a enregistrée avec succès la facture, code: $facture->code";
        return response()->json(['message' => $message]);
    }

    public function getOne(int $id)
    {
        $facture = Facture::with('paiements.moyen', 'articles', 'plats', 'cocktails', 'tournees')->find($id);
        return response()->json(['facture' => $facture]);
    }

    public function update(int $id, Request $request)
    {
        $facture = Facture::find($id);
        $toSync = [];
        foreach ($request->plats as $article) {
            $toSync[$article['id']] = ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']];
        }
        $facture->plats()->sync($toSync);
        $toSync = [];
        foreach ($request->autres as $article) {
            $toSync[$article['id']] = ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']];
        }
        $facture->articles()->sync($toSync);
        $toSync = [];
        foreach ($request->cocktails as $article) {
            $toSync[$article['id']] = ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']];
        }
        $facture->cocktails()->sync($toSync);
        $toSync = [];
        foreach ($request->tournees as $article) {
            $toSync[$article['id']] = ['quantite' => $article['quantite'], 'prix_vente' => $article['prix']];
        }
        $facture->tournees()->sync($toSync);
        $message = "la facture $facture->code a été modifiée avec succès.";
        return response()->json(['message' => $message]);
    }

}
