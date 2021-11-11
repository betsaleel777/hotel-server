DELIMITER |
CREATE PROCEDURE inventaire_externe(IN restaurant BIGINT UNSIGNED )
BEGIN
    SELECT m1.id,m1.nom,m1.prix_unitaire,m1.disponible-IFNULL(m2.sortie,0) AS disponible,IFNULL(m2.reste,0) AS reste
    FROM (SELECT t1.id,t1.nom,t1.prix_unitaire,COALESCE(t1.entree-t2.quantite,t1.entree) AS disponible
    FROM (SELECT ae.id,ae.nom,IFNULL(SUM(ade.quantite),0) AS entree,COALESCE (NULLIF(AVG(ade.cout),0),ae.prix_vente)
    AS prix_unitaire FROM articles_depenses_externes ade RIGHT JOIN articles_externes ae ON ade.article_id = ae.id
    WHERE ae.restaurant_id = restaurant GROUP BY ae.id) AS t1
    LEFT JOIN
    (WITH plat AS (SELECT fpe.plat_id AS id,pe.nom,sum(fpe.quantite) AS sortie FROM factures_plats_externes fpe
    INNER JOIN plats_externes pe ON pe.id=fpe.plat_id WHERE pe.restaurant_id = restaurant GROUP BY fpe.plat_id)
    SELECT ie.article_id AS id,ae.nom,sum(ie.quantite*p.sortie) AS quantite FROM ingredients_externes ie
    INNER JOIN articles_externes ae ON ae.id=ie.article_id INNER JOIN plat p ON p.id = ie.plat_id
    WHERE ae.restaurant_id = restaurant GROUP BY ie.article_id) AS t2 ON t1.id=t2.id) m1 LEFT JOIN
    ((SELECT t1.id,t1.nom,t1.contenance,sum(t1.sortiecl+t2.sortiecl) DIV t1.contenance AS sortie,
    sum(t1.sortiecl+t2.sortiecl) MOD t1.contenance*100/t1.contenance AS reste FROM (WITH tournees AS
    (WITH cocktail AS (SELECT fce.cocktail_id AS id,ce.nom,sum(fce.quantite) AS sortie FROM factures_cocktails_externes fce
    INNER JOIN cocktails_externes ce ON fce.cocktail_id = ce.id GROUP BY fce.cocktail_id)
    SELECT me.tournee_id as id,te.article_id,te.nom,sum(te.nombre*me.quantite*c.sortie*5) AS sortiecl
    FROM melanges_externes me INNER JOIN tournees_externes te ON te.id=me.tournee_id INNER JOIN cocktail c
    ON c.id = me.cocktail_id WHERE te.restaurant_id = restaurant GROUP BY me.tournee_id)
    SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
    WHERE ae.restaurant_id = restaurant GROUP BY ae.id) AS t1
    RIGHT JOIN
    (WITH tournees AS (SELECT fte.tournee_id AS id,te.nom,te.article_id,sum(te.nombre*fte.quantite*5) AS sortiecl
    FROM factures_tournees_externes fte INNER JOIN tournees_externes te ON te.id=fte.tournee_id WHERE te.restaurant_id = restaurant
    GROUP BY fte.tournee_id )
    SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
    WHERE ae.restaurant_id = restaurant GROUP BY ae.id) AS t2  ON t1.id=t2.id GROUP BY t1.id)
    UNION
    (SELECT t1.id,t1.nom,t1.contenance,sum(t1.sortiecl+t2.sortiecl) DIV t1.contenance AS sortie,sum(t1.sortiecl+t2.sortiecl)
    MOD t1.contenance*100/t1.contenance AS reste FROM (WITH tournees AS (WITH cocktail AS
    (SELECT fce.cocktail_id AS id,ce.nom,sum(fce.quantite) AS sortie FROM factures_cocktails_externes fce
    INNER JOIN cocktails_externes ce ON fce.cocktail_id = ce.id GROUP BY fce.cocktail_id)
    SELECT me.tournee_id as id,te.article_id,te.nom,sum(te.nombre*me.quantite*c.sortie*5) AS sortiecl
    FROM melanges_externes me INNER JOIN tournees_externes te ON te.id=me.tournee_id
    LEFT JOIN cocktail c ON c.id = me.cocktail_id WHERE te.restaurant_id = restaurant GROUP BY me.tournee_id)
    SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
    WHERE ae.restaurant_id = restaurant GROUP BY ae.id) AS t1
    RIGHT JOIN
    (WITH tournees AS (SELECT fte.tournee_id AS id,te.nom,te.article_id,sum(te.nombre*fte.quantite*5) AS sortiecl
    FROM factures_tournees_externes fte INNER JOIN tournees_externes te ON te.id=fte.tournee_id
    WHERE te.restaurant_id = restaurant GROUP BY fte.tournee_id )
    SELECT ae.id,ae.nom,ae.contenance,t.sortiecl FROM articles_externes ae INNER JOIN tournees t ON ae.id=t.article_id
    WHERE ae.restaurant_id = restaurant GROUP BY ae.id) AS t2  ON t1.id=t2.id GROUP BY t1.id)) m2 ON m1.id=m2.id
END|
