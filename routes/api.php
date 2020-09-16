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
| Auth Routes
|--------------------------------------------------------------------------
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


/*
|--------------------------------------------------------------------------
| Role Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'roles', 'middleware' => ['auth']], function ($router) {
    $router->get('/', 'RoleController@index');
    $router->get('/{id:\d+}', 'RoleController@show');

    $router->post('/', 'RoleController@store');
    $router->post('/{id:\d+}/permissions', 'RoleController@syncPermissions');
    
    $router->patch('/{id:\d+}', 'RoleController@update');

    $router->delete('/{id:\d+}', 'RoleController@destroy');
});

/*
|--------------------------------------------------------------------------
| Permission Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'permissions', 'middleware' => ['auth']], function ($router) {
    $router->get('/', 'PermissionController@index');
    $router->get('/{id:\d+}', 'PermissionController@show');

    $router->post('/', 'PermissionController@store');
    $router->post('/{id:\d+}/roles', 'PermissionController@syncRoles');
    
    $router->patch('/{id:\d+}', 'PermissionController@update');

    $router->delete('/{id:\d+}', 'PermissionController@destroy');
});

/*
|--------------------------------------------------------------------------
| Category Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'categories', 'middleware' => ['auth']], function ($router) {
    $router->get('/', [
        'uses' => 'CategoryController@index',
        'middleware' => [
            'permission:category.index'
        ]
    ]);

    $router->post('/', [
        'uses' => 'CategoryController@store',
        'middleware' => [
            'permission:category.create'
        ]
    ]);
    
    $router->patch('/{id:\d+}', [
        'uses' => 'CategoryController@update',
        'middleware' => [
            'permission:category.update'
        ]
    ]);

    $router->delete('/{id:\d+}', [
        'uses' => 'CategoryController@destroy',
        'middleware' => [
            'permission:category.delete'
        ]
    ]);
});
