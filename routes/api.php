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
    $router->get('/', [
        'uses' => 'RoleController@index',
        'middleware' => [
            'permission:role.index'
        ]
    ]);
    $router->get('/{id:\d+}', [
        'uses' => 'RoleController@show',
        'middleware' => [
            'permission:role.show'
        ]
    ]);

    $router->post('/', [
        'uses' => 'RoleController@store',
        'middleware' => [
            'permission:role.create'
        ]
    ]);
    $router->post('/{id:\d+}/permissions', [
        'uses' => 'RoleController@syncPermissions',
        'middleware' => [
            'permission:role.update',
        ]
    ]);
    
    $router->patch('/{id:\d+}', [
        'uses' => 'RoleController@update',
        'middleware' => [
            'permission:role.update',
        ]
    ]);

    $router->delete('/{id:\d+}', [
        'uses' => 'RoleController@destroy',
        'middleware' => [
            'permission:role.delete'
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Permission Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'permissions', 'middleware' => ['auth']], function ($router) {
    $router->get('/', [
        'uses' => 'PermissionController@index',
        'middleware' => [
            'permission:permission.index'
        ]
    ]);
    $router->get('/{id:\d+}', [
        'uses' => 'PermissionController@show',
        'middleware' => [
            'permission:permission.show'
        ]
    ]);

    $router->post('/', [
        'uses' => 'PermissionController@store',
        'middleware' => [
            'permission:permission.create'
        ]
    ]);
    $router->post('/{id:\d+}/roles', [
        'uses' => 'PermissionController@syncRoles',
        'middleware' => [
            'permission:permission.update',
        ]
    ]);
    
    $router->patch('/{id:\d+}', [
        'uses' => 'PermissionController@update',
        'middleware' => [
            'permission:permission.update',
        ]
    ]);

    $router->delete('/{id:\d+}', [
        'uses' => 'PermissionController@destroy',
        'middleware' => [
            'permission:permission.delete'
        ]
    ]);
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
