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

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
*/
$router->group(['prefix' => 'auth'], function ($router) {
    $router->group(['middleware' => ['guest']], function ($router) {
        $router->post('login', 'AuthController@login');
    });

    $router->group(['middleware' => ['auth']], function ($router) {
        $router->get('me', 'AuthController@me');
        
        $router->post('refresh', 'AuthController@refresh');
        $router->post('logout', 'AuthController@logout');
    });
});
