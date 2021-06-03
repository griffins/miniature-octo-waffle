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

$router->get('/coupon', 'CouponController@index');
$router->get('/coupon/active', 'CouponController@listActive');
$router->post('/coupon/de-activate/{id}', 'CouponController@deactivate');
$router->post('/coupon', 'CouponController@store');
$router->patch('/coupon/{id}', 'CouponController@update');
$router->delete('/coupon/{id}', 'CouponController@destroy');
$router->post('/coupon/apply', 'CouponController@apply');

