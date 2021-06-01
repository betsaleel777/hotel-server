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

$router->group(['prefix' => 'gestion-chambre'], function () use ($router) {
    $router->group(['prefix' => 'chambres'], function () use ($router) {
        $router->get('/', 'GestionChambre\ChambresController@getAll');
        $router->post('new', 'GestionChambre\ChambresController@insert');
        $router->put('{id}', 'GestionChambre\ChambresController@update');
        $router->delete('{id}', 'GestionChambre\ChambresController@delete');
        //other
        $router->get('/passage', 'GestionChambre\ChambresController@getPassage');
        $router->get('/reservation/{debut}-{fin}', 'GestionChambre\ChambresController@getReservation');
    });

    $router->group(['prefix' => 'categories'], function () use ($router) {
        $router->get('/', 'GestionChambre\CategoriesController@getAll');
        $router->post('new', 'GestionChambre\CategoriesController@insert');
    });
});

$router->group(['prefix' => 'reception'], function () use ($router) {
    $router->group(['prefix' => 'clients'], function () use ($router) {
        $router->get('/', 'Reception\ClientsController@getAll');
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
        $router->post('new', 'Reception\AttributionsController@insert');
        $router->delete('{id}', 'Reception\AttributionsController@delete');
        //other
        $router->put('free/{id}', 'Reception\AttributionsController@liberer');

    });

    $router->group(['prefix' => 'reservations'], function () use ($router) {
        $router->get('/', 'Reception\ReservationsController@getAll');
        $router->get('reserved', 'Reception\ReservationsController@getReserved');
        $router->get('events', 'Reception\ReservationsController@getEvents');
        $router->get('{id}', 'Reception\ReservationsController@getOne');
        $router->post('new', 'Reception\ReservationsController@insert');
        $router->delete('{id}', 'Reception\ReservationsController@delete');
        //other
        $router->put('abort/{id}', 'Reception\ReservationsController@annuler');

    });
});

$router->group(['prefix' => 'stock'], function () use ($router) {

    $router->group(['prefix' => 'produits'], function () use ($router) {
        $router->get('/', 'Stock\ProduitsController@getAll');
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
        $router->get('reject/{id}', 'Stock\DemandesController@reject');
        $router->get('deliver/{id}', 'Stock\DemandesController@deliver');
        $router->get('inventaire/{departement}', 'Stock\DemandesController@inventaire');
        $router->post('new', 'Stock\DemandesController@insert');
        $router->post('sortie', 'Stock\DemandesController@insertSortie');
        $router->post('cloner', 'Stock\DemandesController@cloner');
        $router->put('{id}', 'Stock\DemandesController@update');
        $router->put('accept/{id}', 'Stock\DemandesController@accept');
        $router->delete('{id}', 'Stock\DemandesController@delete');
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

$router->group(['prefix' => 'parametre'], function () use ($router) {

    $router->group(['prefix' => 'departements'], function () use ($router) {
        $router->get('/', 'Parametre\DepartementsController@getAll');
        $router->get('/{name}', 'Parametre\DepartementsController@getByName');
        $router->post('new', 'Parametre\DepartementsController@insert');
        $router->put('{id}', 'Parametre\DepartementsController@update');
        $router->delete('{id}', 'Parametre\DepartementsController@delete');
    });
});
