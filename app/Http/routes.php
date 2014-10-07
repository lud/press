<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/



$router->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
$router->get('article/{year}/{month}/{day}/{slug}', 'PressController@publish');
$router->get('page/{slug}', 'PressController@publish');
