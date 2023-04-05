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

$router->post('/auth/login', 'AuthController@signIn');
$router->post('/auth/register', 'AuthController@signUp');
$router->get('/auth/logout', 'AuthController@signOut');

$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->get('products', 'Controller@index');
});
