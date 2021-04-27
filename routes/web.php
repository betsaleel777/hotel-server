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
    });

    $router->group(['prefix' => 'categories'], function () use ($router) {
        $router->get('/', 'GestionChambre\CategoriesController@getAll');
        $router->post('new', 'GestionChambre\CategoriesController@insert');
    });
});
