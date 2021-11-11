<?php

namespace App\Http\Controllers\Externe\Stock\Article;

use App\Http\Controllers\Controller;
use App\Models\Externe\Stock\Article\Article;
use App\Models\Externe\Stock\Article\Prix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $article->save();
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

    public function getArticlesTourneeFromRestau(int $restaurant)
    {
        $articles = Article::with('categorie')->where('restaurant_id', $restaurant)->tournable()->get();
        return response()->json(['articles' => $articles]);
    }

    public function getArticlesPlatFromRestau(int $restaurant)
    {
        $articles = Article::with('categorie')->where('restaurant_id', $restaurant)->preparable()->get();
        return response()->json(['articles' => $articles]);
    }

    public function inventaireFromRestau(int $restaurant)
    {
        $articles = DB::select(
            DB::raw("SELECT m1.id,m1.nom,m1.prix_unitaire,m1.disponible-IFNULL(m2.sortie,0) AS disponible,IFNULL(m2.reste,0) AS reste FROM (SELECT t1.id,t1.nom,t1.prix_unitaire,COALESCE(t1.entree-t2.quantite,t1.entree) AS disponible FROM (SELECT ae.id,ae.nom,IFNULL(SUM(ade.quantite),0) AS entree,COALESCE (NULLIF(AVG(ade.cout),0),ae.prix_vente) AS prix_unitaire FROM articles_depenses_externes ade RIGHT JOIN articles_externes ae ON ade.article_id = ae.id
WHERE ae.restaurant_id=$restaurant GROUP BY ae.id) AS t1
LEFT JOIN
(WITH plat AS (SELECT fpe.plat_id AS id,pe.nom,sum(fpe.quantite) AS sortie FROM factures_plats_externes fpe INNER JOIN plats_externes pe ON pe.id=fpe.plat_id WHERE pe.restaurant_id=$restaurant
GROUP BY fpe.plat_id) SELECT ie.article_id AS id,ae.nom,sum(ie.quantite*p.sortie) AS quantite FROM ingredients_externes ie INNER JOIN articles_externes ae ON ae.id=ie.article_id
INNER JOIN plat p ON p.id = ie.plat_id WHERE ae.restaurant_id = $restaurant GROUP BY ie.article_id) AS t2 ON t1.id=t2.id) m1 LEFT JOIN
((SELECT t1.id,t1.nom,t1.contenance,sum(t1.sortiecl+t2.sortiecl) DIV t1.contenance AS sortie,100 - sum(t1.sortiecl+t2.sortiecl) MOD t1.contenance*100/t1.contenance AS reste
FROM (WITH tournees AS (WITH cocktail AS (SELECT fce.cocktail_id AS id,ce.nom,sum(fce.quantite) AS sortie FROM factures_cocktails_externes fce INNER JOIN cocktails_externes ce
ON fce.cocktail_id = ce.id GROUP BY fce.cocktail_id)
SELECT me.tournee_id as id,te.article_id,te.nom,sum(te.nombre*me.quantite*c.sortie*5) AS sortiecl FROM melanges_externes me INNER JOIN tournees_externes te ON te.id=me.tournee_id
INNER JOIN cocktail c ON c.id = me.cocktail_id WHERE te.restaurant_id = $restaurant GROUP BY me.tournee_id)
SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
WHERE ae.restaurant_id = $restaurant GROUP BY ae.id) AS t1
RIGHT JOIN
(WITH tournees AS (SELECT fte.tournee_id AS id,te.nom,te.article_id,sum(te.nombre*fte.quantite*5) AS sortiecl FROM factures_tournees_externes fte INNER JOIN tournees_externes te ON te.id=fte.tournee_id WHERE te.restaurant_id=$restaurant
GROUP BY fte.tournee_id )
SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
WHERE ae.restaurant_id = $restaurant GROUP BY ae.id) AS t2  ON t1.id=t2.id GROUP BY t1.id)
UNION
(SELECT t1.id,t1.nom,t1.contenance,sum(t1.sortiecl+t2.sortiecl) DIV t1.contenance AS sortie,100 - sum(t1.sortiecl+t2.sortiecl) MOD t1.contenance*100/t1.contenance AS reste
FROM (WITH tournees AS (WITH cocktail AS (SELECT fce.cocktail_id AS id,ce.nom,sum(fce.quantite) AS sortie FROM factures_cocktails_externes fce INNER JOIN cocktails_externes ce
ON fce.cocktail_id = ce.id GROUP BY fce.cocktail_id)
SELECT me.tournee_id as id,te.article_id,te.nom,sum(te.nombre*me.quantite*c.sortie*5) AS sortiecl FROM melanges_externes me INNER JOIN tournees_externes te ON te.id=me.tournee_id
LEFT JOIN cocktail c ON c.id = me.cocktail_id WHERE te.restaurant_id = $restaurant GROUP BY me.tournee_id)
SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
WHERE ae.restaurant_id = $restaurant GROUP BY ae.id) AS t1
RIGHT JOIN
(WITH tournees AS (SELECT fte.tournee_id AS id,te.nom,te.article_id,sum(te.nombre*fte.quantite*5) AS sortiecl FROM factures_tournees_externes fte INNER JOIN tournees_externes te ON te.id=fte.tournee_id WHERE te.restaurant_id=$restaurant
GROUP BY fte.tournee_id )
SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
WHERE ae.restaurant_id = $restaurant GROUP BY ae.id) AS t2  ON t1.id=t2.id GROUP BY t1.id)) m2 ON m1.id=m2.id")
        );
        return response()->json(['articles' => $articles]);
    }
}
