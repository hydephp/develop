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

$router->get('/', 'DashboardController@index');

$router->get('/version', function () use ($router) {
    return $router->app->version();
});

$router->get('/debug', 'DebugController');

$router->post('/fileapi/open', 'FilesystemController@open');
$router->get('/open/_site', 'RealtimeCompiler@render');
