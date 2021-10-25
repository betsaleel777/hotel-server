<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => ['auth']], function ($router) {
    $router->post('logout', 'Auth\AuthController@logout');
    $router->post('refresh', 'Auth\AuthController@refresh');
    $router->get('user', 'Auth\AuthController@profile');
});
$router->post('login', 'Auth\AuthController@login');

$router->group(['prefix' => 'gestion-chambre'], function () use ($router) {
    $router->group(['prefix' => 'chambres'], function () use ($router) {
        $router->get('/', 'GestionChambre\ChambresController@getAll');
        $router->get('{id}', 'GestionChambre\ChambresController@getOne');
        $router->get('/reservation/{debut}-{fin}', 'GestionChambre\ChambresController@getReservation');
        $router->post('new', 'GestionChambre\ChambresController@insert');
        $router->put('{id}', 'GestionChambre\ChambresController@update');
        $router->delete('{id}', 'GestionChambre\ChambresController@delete');
        //other
    });
    $router->group(['prefix' => 'categories'], function () use ($router) {
        $router->get('/', 'GestionChambre\CategoriesController@getAll');
        $router->post('new', 'GestionChambre\CategoriesController@insert');
        $router->put('{id}', 'GestionChambre\CategoriesController@update');
        $router->delete('{id}', 'GestionChambre\CategoriesController@delete');
    });
});

$router->group(['prefix' => 'reception'], function () use ($router) {
    $router->group(['prefix' => 'clients'], function () use ($router) {
        $router->get('/', 'Reception\ClientsController@getAll');
        $router->get('{id}', 'Reception\ClientsController@getOne');
        $router->post('new', 'Reception\ClientsController@insert');
        $router->put('{id}', 'Reception\ClientsController@update');
        $router->delete('{id}', 'Reception\ClientsController@delete');
    });

    $router->group(['prefix' => 'pieces'], function () use ($router) {
        $router->get('/', 'Reception\PiecesController@getAll');
        $router->post('new', 'Reception\PiecesController@insert');
        $router->put('{id}', 'Reception\PiecesController@update');
        $router->delete('{id}', 'Reception\PiecesController@delete');
    });

    $router->group(['prefix' => 'attributions'], function () use ($router) {
        $router->get('/', 'Reception\AttributionsController@getAll');
        $router->get('/busy', 'Reception\AttributionsController@getBusy');
        $router->get('/{id}', 'Reception\AttributionsController@getOne');
        $router->post('new', 'Reception\AttributionsController@insert');
        $router->put('{id}', 'Reception\AttributionsController@update');
        $router->put('free/{id}', 'Reception\AttributionsController@liberer');
        $router->put('event/{id}', 'Reception\AttributionsController@updateCalendar');
        $router->delete('{id}', 'Reception\AttributionsController@delete');
    });

    $router->group(['prefix' => 'reservations'], function () use ($router) {
        $router->get('/', 'Reception\ReservationsController@getAll');
        $router->get('reserved', 'Reception\ReservationsController@getReserved');
        $router->get('events', 'Reception\ReservationsController@getEvents');
        $router->get('/used', 'Reception\ReservationsController@utilisees');
        $router->get('{id}', 'Reception\ReservationsController@getOne');
        $router->post('new', 'Reception\ReservationsController@insert');
        $router->delete('{id}', 'Reception\ReservationsController@delete');
        //other
        $router->put('{id}', 'Reception\ReservationsController@update');
        $router->put('abort/{id}', 'Reception\ReservationsController@annuler');
    });

    $router->group(['prefix' => 'encaissements'], function () use ($router) {
        $router->get('/', 'Reception\EncaissementsController@getAll');
        $router->get('/soldes', 'Reception\EncaissementsController@getSoldes');
        $router->get('/non-soldes', 'Reception\EncaissementsController@getNonSoldes');
        $router->get('{date}', 'Reception\EncaissementsController@getByDate');
        $router->post('new', 'Reception\EncaissementsController@insert');
        $router->put('{id}', 'Reception\EncaissementsController@update');
        $router->delete('{id}', 'Reception\EncaissementsController@delete');
    });
});

$router->group(['prefix' => 'stock'], function () use ($router) {
    $router->group(['prefix' => 'produits'], function () use ($router) {
        $router->get('/', 'Stock\ProduitsController@getAll');
        $router->get('/inventaire', 'Stock\ProduitsController@inventaire');
        $router->get('/inventaire/sortie', 'Stock\ProduitsController@inventaireSortie');
        $router->get('/plats', 'Stock\ProduitsController@getPlatProducts');
        $router->get('/boissons', 'Stock\ProduitsController@getBoissonProducts');
        $router->get('/tournees', 'Stock\ProduitsController@getTourneesProducts');
        $router->post('new', 'Stock\ProduitsController@insert');
        $router->put('{id}', 'Stock\ProduitsController@update');
        $router->delete('{id}', 'Stock\ProduitsController@delete');
    });
    $router->group(['prefix' => 'achats'], function () use ($router) {
        $router->get('/', 'Stock\AchatsController@getAll');
        $router->post('new', 'Stock\AchatsController@insert');
        $router->put('{id}', 'Stock\AchatsController@update');
        $router->delete('{id}', 'Stock\AchatsController@delete');
        //other
        $router->get('en-stock/{id}', 'Stock\AchatsController@quantiteStock');
        $router->get('produit/{id}', 'Stock\AchatsController@getFromProduit');
    });
    $router->group(['prefix' => 'categories'], function () use ($router) {
        $router->get('/', 'Stock\CategoriesController@getAll');
        $router->post('new', 'Stock\CategoriesController@insert');
        $router->put('{id}', 'Stock\CategoriesController@update');
        $router->delete('{id}', 'Stock\CategoriesController@delete');
    });
    $router->group(['prefix' => 'demandes'], function () use ($router) {
        $router->get('/', 'Stock\DemandesController@getAll');
        $router->get('/{departement}', 'Stock\DemandesController@getByDepartement');
        $router->get('produits/{departement}', 'Stock\DemandesController@getProductsByDepartement');
        $router->get('reject/{id}', 'Stock\DemandesController@reject');
        $router->get('inventaire/{departement}', 'Stock\DemandesController@inventaire');
        $router->get('inventaire/buvable/{departement}', 'Stock\DemandesController@inventaireBuvable');
        $router->get('traitement/{id}', 'Stock\DemandesController@traitement');
        $router->post('new', 'Stock\DemandesController@insert');
        $router->put('accept/{id}', 'Stock\DemandesController@accept');
    });
    $router->group(['prefix' => 'sorties'], function () use ($router) {
        $router->get('/', 'Stock\SortiesController@getAll');
        $router->get('from/{demande}', 'Stock\SortiesController@getFromDemande');
        $router->put('{id}', 'Stock\SortiesController@update');
        $router->put('confirm/{id}', 'Stock\SortiesController@confirm');
        $router->post('demande', 'Stock\DemandesController@insertFromDemande');
        $router->post('new', 'Stock\SortiesController@insert');
    });
});

$router->group(['prefix' => 'restaurant'], function () use ($router) {

    $router->group(['prefix' => 'plats'], function () use ($router) {
        $router->get('/', 'Restaurant\PlatsController@getAll');
        $router->get('/{id}', 'Restaurant\PlatsController@getOne');
        $router->post('new', 'Restaurant\PlatsController@insert');
        $router->put('{id}', 'Restaurant\PlatsController@update');
        $router->delete('{id}', 'Restaurant\PlatsController@delete');
        //other
        $router->post('/prix-minimal', 'Restaurant\PlatsController@prixMinimal');
    });

    $router->group(['prefix' => 'categories'], function () use ($router) {
        $router->get('/', 'Restaurant\CategoriesController@getAll');
        $router->get('/{id}', 'Restaurant\CategoriesController@getOne');
        $router->post('new', 'Restaurant\CategoriesController@insert');
        $router->put('{id}', 'Restaurant\CategoriesController@update');
        $router->delete('{id}', 'Restaurant\CategoriesController@delete');
    });

});

$router->group(['prefix' => 'bar'], function () use ($router) {
    $router->group(['prefix' => 'cocktails'], function () use ($router) {
        $router->get('/', 'Bar\CocktailsController@getAll');
        $router->get('/{id}', 'Bar\CocktailsController@getOne');
        $router->post('new', 'Bar\CocktailsController@insert');
        $router->put('{id}', 'Bar\CocktailsController@update');
        $router->delete('{id}', 'Bar\CocktailsController@delete');
    });
    $router->group(['prefix' => 'tournees'], function () use ($router) {
        $router->get('/', 'Bar\TourneesController@getAll');
        $router->get('/{id}', 'Bar\TourneesController@getOne');
        $router->post('new', 'Bar\TourneesController@insert');
        $router->put('{id}', 'Bar\TourneesController@update');
        $router->delete('{id}', 'Bar\TourneesController@delete');
    });

});

$router->group(['prefix' => 'caisses'], function () use ($router) {
    $router->group(['prefix' => 'encaissements'], function () use ($router) {
        $router->get('/', 'Caisse\EncaissementsController@getAll');
        $router->get('/{id}', 'Caisse\EncaissementsController@getOne');
        $router->get('/departement/{id}', 'Caisse\EncaissementsController@getByDepartement');
        $router->get('/soldes/departement/{id}', 'Caisse\EncaissementsController@getSoldesByDepartement');
        $router->get('finance/{departement}', 'Caisse\EncaissementsController@pointFinancierStandard');
        $router->get('finance/{departement}/{debut}/{fin}', 'Caisse\EncaissementsController@pointFinancierIntervalleDate');
        $router->get('finance/{departement}/{jour}/', 'Caisse\EncaissementsController@pointFinancierJournalier');
        $router->post('new', 'Caisse\EncaissementsController@insert');
        $router->post('versement', 'Caisse\EncaissementsController@encaisser');
        $router->put('{id}', 'Caisse\EncaissementsController@update');
        $router->delete('{id}', 'Caisse\EncaissementsController@delete');
    });
    $router->group(['prefix' => 'mobilesMoney'], function () use ($router) {
        $router->get('/', 'Caisse\MobileMoneyController@getAll');
        $router->post('new', 'Caisse\MobileMoneyController@insert');
        $router->put('{id}', 'Caisse\MobileMoneyController@update');
        $router->delete('{id}', 'Caisse\MobileMoneyController@delete');
    });
});

$router->group(['prefix' => 'parametre'], function () use ($router) {
    $router->group(['prefix' => 'departements'], function () use ($router) {
        $router->get('/', 'Parametre\DepartementsController@getAll');
        $router->get('/{name}', 'Parametre\DepartementsController@getByName');
        $router->post('new', 'Parametre\DepartementsController@insert');
        $router->put('{id}', 'Parametre\DepartementsController@update');
        $router->delete('{id}', 'Parametre\DepartementsController@delete');
    });

    $router->group(['prefix' => 'categories'], function () use ($router) {
        $router->group(['prefix' => 'chambres'], function () use ($router) {
            $router->get('/', 'GestionChambre\CategoriesController@getAll');
            $router->get('/{name}', 'GestionChambre\CategoriesController@getByName');
            $router->post('new', 'GestionChambre\CategoriesController@insert');
            $router->put('{id}', 'GestionChambre\CategoriesController@update');
            $router->delete('{id}', 'GestionChambre\CategoriesController@delete');
        });
        $router->group(['prefix' => 'articles'], function () use ($router) {
            $router->get('/', 'Stock\CategoriesController@getAll');
            $router->get('/{name}', 'Stock\CategoriesController@getByName');
            $router->post('new', 'Stock\CategoriesController@insert');
            $router->put('{id}', 'Stock\CategoriesController@update');
            $router->delete('{id}', 'Stock\CategoriesController@delete');
        });
        $router->group(['prefix' => 'plats'], function () use ($router) {
            $router->get('/', 'Restaurant\CategoriesController@getAll');
            $router->get('/{name}', 'Restaurant\CategoriesController@getByName');
            $router->post('new', 'Restaurant\CategoriesController@insert');
            $router->put('{id}', 'Restaurant\CategoriesController@update');
            $router->delete('{id}', 'Restaurant\CategoriesController@delete');
        });
    });

    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/', 'Parametre\UsersController@getAll');
        $router->get('/{id}', 'Parametre\UsersController@getOne');
        $router->post('new', 'Parametre\UsersController@insert');
        $router->put('{id}', 'Parametre\UsersController@update');
        $router->delete('{id}', 'Parametre\UsersController@delete');
    });

    $router->group(['prefix' => 'roles'], function () use ($router) {
        $router->get('/', 'Parametre\RolesController@getAll');
        $router->get('/{id}', 'Parametre\RolesController@getOne');
        $router->post('new', 'Parametre\RolesController@insert');
        $router->post('assign', 'Parametre\RolesController@assign');
        $router->put('{id}', 'Parametre\RolesController@update');
        $router->delete('{id}', 'Parametre\RolesController@delete');
    });

    $router->group(['prefix' => 'permissions'], function () use ($router) {
        $router->get('/', 'Parametre\PermissionsController@getAll');
        $router->get('/{id}', 'Parametre\PermissionsController@getOne');
        $router->post('new', 'Parametre\PermissionsController@insert');
        $router->put('{id}', 'Parametre\PermissionsController@update');
        $router->delete('{id}', 'Parametre\PermissionsController@delete');
    });
});
// routes pour la gestion du restaurant externe ------------------------------------------------------------------------------------

$router->group(['prefix' => 'externe'], function ($router) {
    $router->group(['prefix' => 'parametre'], function ($router) {
        $router->group(['prefix' => 'restaurants'], function ($router) {
            $router->get('/', 'Externe\Parametre\RestaurantsController@getAll');
            $router->get('{id}', 'Externe\Parametre\RestaurantsController@getOne');
            $router->post('new', 'Externe\Parametre\RestaurantsController@insert');
            $router->put('{id}', 'Externe\Parametre\RestaurantsController@update');
            $router->delete('{id}', 'Externe\Parametre\RestaurantsController@delete');
        });
        $router->group(['prefix' => 'categories'], function ($router) {
            $router->group(['prefix' => 'articles'], function ($router) {
                $router->get('/', 'Externe\Stock\Article\CategoriesController@getAll');
                $router->get('restaurant/{restaurant}', 'Externe\Stock\Article\CategoriesController@getFromRestau');
                $router->get('{id}', 'Externe\Stock\Article\CategoriesController@getOne');
                $router->post('new', 'Externe\Stock\Article\CategoriesController@insert');
                $router->put('{id}', 'Externe\Stock\Article\CategoriesController@update');
                $router->delete('{id}', 'Externe\Stock\Article\CategoriesController@delete');

            });
            $router->group(['prefix' => 'plats'], function ($router) {
                $router->get('/', 'Externe\Stock\Plat\CategoriesController@getAll');
                $router->get('restaurant/{restaurant}', 'Externe\Stock\Plat\CategoriesController@getFromRestau');
                $router->get('{id}', 'Externe\Stock\Plat\CategoriesController@getOne');
                $router->post('new', 'Externe\Stock\Plat\CategoriesController@insert');
                $router->put('{id}', 'Externe\Stock\Plat\CategoriesController@update');
                $router->delete('{id}', 'Externe\Stock\Plat\CategoriesController@delete');

            });
            $router->group(['prefix' => 'tournees'], function ($router) {
                $router->get('/', 'Externe\Stock\Tournee\CategoriesController@getAll');
                $router->get('restaurant/{restaurant}', 'Externe\Stock\Tournee\CategoriesController@getFromRestau');
                $router->get('{id}', 'Externe\Stock\Tournee\CategoriesController@getOne');
                $router->post('new', 'Externe\Stock\Tournee\CategoriesController@insert');
                $router->put('{id}', 'Externe\Stock\Tournee\CategoriesController@update');
                $router->delete('{id}', 'Externe\Stock\Tournee\CategoriesController@delete');

            });
            $router->group(['prefix' => 'cocktails'], function ($router) {
                $router->get('/', 'Externe\Stock\Cocktail\CategoriesController@getAll');
                $router->get('restaurant/{restaurant}', 'Externe\Stock\Cocktail\CategoriesController@getFromRestau');
                $router->get('{id}', 'Externe\Stock\Cocktail\CategoriesController@getOne');
                $router->post('new', 'Externe\Stock\Cocktail\CategoriesController@insert');
                $router->put('{id}', 'Externe\Stock\Cocktail\CategoriesController@update');
                $router->delete('{id}', 'Externe\Stock\Cocktail\CategoriesController@delete');

            });
        });
    });
    $router->group(['prefix' => 'stock'], function ($router) {
        $router->group(['prefix' => 'articles'], function ($router) {
            $router->get('/', 'Externe\Stock\Article\ArticlesController@getAll');
            $router->get('restaurant/{restaurant}', 'Externe\Stock\Article\ArticlesController@getFromRestau');
            $router->get('restaurant/trashed/{restaurant}', 'Externe\Stock\Article\ArticlesController@getTrashedFromRestau');
            $router->get('tournee/{restaurant}', 'Externe\Stock\Article\ArticlesController@getArticlesTourneeFromRestau');
            $router->get('plat/{restaurant}', 'Externe\Stock\Article\ArticlesController@getArticlesPlatFromRestau');
            $router->get('inventory/{restaurant}', 'Externe\Stock\Article\ArticlesController@inventaireFromRestau');
            $router->get('{id}', 'Externe\Stock\Article\ArticlesController@getOne');
            $router->get('restorer/{id}', 'Externe\Stock\Article\ArticlesController@restorer');
            $router->post('new', 'Externe\Stock\Article\ArticlesController@insert');
            $router->put('{id}', 'Externe\Stock\Article\ArticlesController@update');
            $router->delete('archiver/{id}', 'Externe\Stock\Article\ArticlesController@trash');
            $router->delete('{id}', 'Externe\Stock\Article\ArticlesController@delete');
        });
        $router->group(['prefix' => 'plats'], function ($router) {
            $router->get('/', 'Externe\Stock\Plat\PlatsController@getAll');
            $router->get('restaurant/{restaurant}', 'Externe\Stock\Plat\PlatsController@getFromRestau');
            $router->get('restaurant/trashed/{restaurant}', 'Externe\Stock\Plat\PlatsController@getTrashedFromRestau');
            $router->get('{id}', 'Externe\Stock\Plat\PlatsController@getOne');
            $router->post('new', 'Externe\Stock\Plat\PlatsController@insert');
            $router->put('{id}', 'Externe\Stock\Plat\PlatsController@update');
            $router->delete('{id}', 'Externe\Stock\Plat\PlatsController@delete');
            $router->delete('archiver/{id}', 'Externe\Stock\Plat\PlatsController@trash');
        });
        $router->group(['prefix' => 'cocktails'], function ($router) {
            $router->get('/', 'Externe\Stock\Cocktail\CocktailsController@getAll');
            $router->get('restaurant/{restaurant}', 'Externe\Stock\Cocktail\CocktailsController@getFromRestau');
            $router->get('restaurant/trashed/{restaurant}', 'Externe\Stock\Cocktail\CocktailsController@getTrashedFromRestau');
            $router->get('restorer/{id}', 'Externe\Stock\Cocktail\CocktailsController@restorer');
            $router->get('{id}', 'Externe\Stock\Cocktail\CocktailsController@getOne');
            $router->post('new', 'Externe\Stock\Cocktail\CocktailsController@insert');
            $router->put('{id}', 'Externe\Stock\Cocktail\CocktailsController@update');
            $router->delete('{id}', 'Externe\Stock\Cocktail\CocktailsController@delete');
            $router->delete('archiver/{id}', 'Externe\Stock\Cocktail\CocktailsController@trash');
        });
        $router->group(['prefix' => 'tournees'], function ($router) {
            $router->get('/', 'Externe\Stock\Tournee\TourneesController@getAll');
            $router->get('restaurant/{restaurant}', 'Externe\Stock\Tournee\TourneesController@getFromRestau');
            $router->get('restaurant/trashed/{restaurant}', 'Externe\Stock\Tournee\TourneesController@getTrashedFromRestau');
            $router->get('restorer/{id}', 'Externe\Stock\Tournee\TourneesController@restorer');
            $router->get('{id}', 'Externe\Stock\Tournee\TourneesController@getOne');
            $router->post('new', 'Externe\Stock\Tournee\TourneesController@insert');
            $router->put('{id}', 'Externe\Stock\Tournee\TourneesController@update');
            $router->delete('{id}', 'Externe\Stock\Tournee\TourneesController@delete');
            $router->delete('archiver/{id}', 'Externe\Stock\Tournee\TourneesController@trash');
        });
        $router->group(['prefix' => 'depenses'], function ($router) {
            $router->get('/', 'Externe\Stock\Depense\DepensesController@getAll');
            $router->get('restaurant/{restaurant}', 'Externe\Stock\Depense\DepensesController@getFromRestau');
            $router->get('restaurant/{restaurant}/date/{date}', 'Externe\Stock\Depense\DepensesController@getFromRestauByDate');
            $router->get('restaurant/trashed/{restaurant}', 'Externe\Stock\Depense\DepensesController@getTrashedFromRestau');
            $router->get('restaurant/trashed/{restaurant}/date/{date}', 'Externe\Stock\Depense\DepensesController@getTrashedFromRestauByDate');
            $router->get('restorer/{id}', 'Externe\Stock\Depense\DepensesController@restorer');
            $router->get('{id}', 'Externe\Stock\Depense\DepensesController@getOne');
            $router->post('new', 'Externe\Stock\Depense\DepensesController@insert');
            $router->put('{id}', 'Externe\Stock\Depense\DepensesController@update');
            $router->delete('{id}', 'Externe\Stock\Depense\DepensesController@delete');
            $router->delete('archiver/{id}', 'Externe\Stock\Depense\DepensesController@trash');
        });
    });
    $router->group(['prefix' => 'caisse'], function ($router) {
        $router->group(['prefix' => 'factures'], function ($router) {
            $router->get('/', 'Externe\Stock\FacturesController@getAll');
            $router->get('{id}', 'Externe\Stock\FacturesController@getOne');
            $router->post('new', 'Externe\Stock\FacturesController@insert');
            $router->put('{id}', 'Externe\Stock\FacturesController@update');
            $router->delete('{id}', 'Externe\Stock\FacturesController@delete');
        });
        $router->group(['prefix' => 'paiements'], function ($router) {
            $router->get('/', 'Externe\Stock\PaiementsController@getAll');
            $router->get('{id}', 'Externe\Stock\PaiementsController@getOne');
            $router->post('new', 'Externe\Stock\PaiementsController@insert');
            $router->put('{id}', 'Externe\Stock\PaiementsController@update');
            $router->delete('{id}', 'Externe\Stock\PaiementsController@delete');
        });
    });
});
