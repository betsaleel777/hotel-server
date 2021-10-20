<?php

namespace App\Http\Controllers\Externe\Stock\Article;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Article\Article;
use App\Models\Externe\Stock\Article\Prix;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function getAll()
    {
        $articles = Article::with('categorie')->get();
        return response()->json(['articles' => $articles]);
    }

    public function getTrashedFromRestau(int $restaurant)
    {
        $articles = Article::onlyTrashed()->with('categorie')->where('restaurant_id', $restaurant)->get();
        return response()->json(['articles' => $articles]);
    }

    public function getFromRestau(int $restaurant)
    {
        $articles = Article::with('categorie')->where('restaurant_id', $restaurant)->get();
        return response()->json(['articles' => $articles]);
    }

    public function getOne(int $id)
    {
        $article = Article::with('categorie')->find($id);
        return response()->json(['article' => $article]);
    }

    public function insert(Request $request)
    {
        $this->validate($request, Article::RULES);
        $article = new Article($request->all());
        $article->genererCode();
        $article->save();
        $prix = new Prix(['montant' => $request->prix_vente, 'article_id' => $article->id, 'restaurant_id' => $request->restaurant_id]);
        $prix->save();
        $message = "l'article $article->nom a été crée avec succès.";
        return response()->json(['message' => $message]);
    }

    public function update(int $id, Request $request)
    {
        $this->validate($request, Article::regles($id));
        $article = Article::find($id);
        $article->fill($request->all());
        if ($article->isDirty('prix_vente')) {
            $prix = new Prix(['montant' => $request->prix_vente, 'article_id' => $id, 'restaurant_id' => $request->restaurant_id]);
            $prix->save();
        }
        $article->update($request->all());
        $message = "l'article $article->nom a été modifié avec succès.";
        return response()->json(['message' => $message]);
    }

    public function restorer(int $id)
    {
        $article = Article::withTrashed()->find($id);
        $article->restore();
        $message = "l'article $article->nom a été restauré avec succès.";
        return response()->json(['message' => $message]);
    }

    public function trash(int $id)
    {
        $article = Article::find($id);
        $article->delete();
        $message = "l'article $article->nom a été archivé avec succès.";
        return response()->json(['message' => $message]);
    }

    public function delete(int $id)
    {
        $article = Article::withTrashed()->find($id);
        $article->forceDelete();
        $message = "l'article $article->nom a été définitivement supprimé.";
        return response()->json(['message' => $message]);
    }

    public function getArticlesTournee()
    {
        $articles = Article::with('categorie')->tournable()->get();
        return response()->json(['articles' => $articles]);
    }

    public function getArticlesPlat()
    {
        $articles = Article::with('categorie')->preparable()->get();
        return response()->json(['articles' => $articles]);
    }
}
