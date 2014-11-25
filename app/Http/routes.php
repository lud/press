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



$router->group(['pressCache' => true, 'namespace' => 'Lud\Press'], function($router){
	$router->get('/', ['uses' => 'PressController@home', 'as' => 'press::home']);
	// canonical for first page of all articles (home list)
	$router->get('/p/1', function(){return Redirect::route('press::home');});
	$router->get('/p/{page}', ['uses' => 'PressController@home'])
		->where('page', '[0-9]+')
		;
	$router->get('article/{year}/{month}/{day}/{slug}', 'PressController@publish')
		// ->where(['year' => '[0-9]{4}','month' => '[0-9]{2}','day' => '[0-9]{2}'])
		;
	$router->get('page/{slug}', 'PressController@publish');
});

$router->group(['__middleware' => 'auth', 'namespace' => 'Lud\Press'], function($router){
	$router->get('editing', ['uses' => 'PressController@startEditing', 'as' => 'press.editing']);
	$router->get('stop-editing', ['uses' => 'PressController@stopEditing', 'as' => 'press.stop_editing']);
	$router->get('refresh/{key}', ['uses' => 'PressController@refresh', 'as' => 'press.refresh_page_cache']);
	$router->get('purge', ['uses' => 'PressController@purge', 'as' => 'press.purge_cache']);
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

$router->group(['namespace' => 'App\Http\Controllers',], function($router){
	$router->controller('auth', 'AuthController');
	$router->controller('password', 'PasswordController');
});
