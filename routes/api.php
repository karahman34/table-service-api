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

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'users', 'middleware' => ['auth']], function ($router) {
    $router->get('/', [
        'uses' => 'UserController@index',
        'middleware' => [
            'permission:user.index'
        ]
    ]);

    $router->get('/{id:\d+}', [
        'uses' => 'UserController@show',
        'middleware' => [
            'permission:user.show'
        ]
    ]);

    $router->post('/', [
        'uses' => 'UserController@store',
        'middleware' => [
            'permission:user.create'
        ]
    ]);

    $router->post('/{id:\d+}/roles', [
        'uses' => 'UserController@syncRoles',
        'middleware' => [
            'permission:user.update',
        ]
    ]);
    
    $router->patch('/{id:\d+}', [
        'uses' => 'UserController@update',
        'middleware' => [
            'permission:user.update'
        ]
    ]);

    $router->delete('/{id:\d+}', [
        'uses' => 'UserController@destroy',
        'middleware' => [
            'permission:user.delete'
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Food Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'foods', 'middleware' => ['auth']], function ($router) {
    $router->get('/', [
        'uses' => 'FoodController@index',
        'middleware' => [
            'permission:food.index'
        ]
    ]);

    $router->get('/{id:\d+}', [
        'uses' => 'FoodController@show',
        'middleware' => [
            'permission:food.show'
        ]
    ]);

    $router->post('/', [
        'uses' => 'FoodController@store',
        'middleware' => [
            'permission:food.create'
        ]
    ]);
    
    $router->patch('/{id:\d+}', [
        'uses' => 'FoodController@update',
        'middleware' => [
            'permission:food.update'
        ]
    ]);

    $router->patch('/{id:\d+}/update-image', [
        'uses' => 'FoodController@updateImage',
        'middleware' => [
            'permission:food.update'
        ]
    ]);

    $router->delete('/{id:\d+}', [
        'uses' => 'FoodController@destroy',
        'middleware' => [
            'permission:food.delete'
        ]
    ]);
});
