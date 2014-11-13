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

$router->group(['pressCache' => true], function($router){
	$router->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
	$router->get('qzd', ['uses' => 'HomeController@index', 'as' => 'home']);
	$router->get('article/{year}/{month}/{day}/{slug}', 'PressController@publish');
	$router->get('page/{slug}', 'PressController@publish');
});


/*
|--------------------------------------------------------------------------
| Authentication & Password Reset Controllers
|--------------------------------------------------------------------------
|
| These two controllers handle the authentication of the users of your
| application, as well as the functions necessary for resetting the
| passwords for your users. You may modify or remove these files.
|
*/

$router->controller('auth', 'AuthController');

$router->controller('password', 'PasswordController');
