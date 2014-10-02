<?php namespace App\Http\Controllers;

use Config;
use Illuminate\Routing\Controller;
use Novel;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@index');
	|
	*/

	public function index()
	{
		$articles = Novel::query('tags:diffusion.page:2:2')->all();
		pre($articles,"articles");
		pre("count", Novel::query('tags:diffusion.count'));
		return \View::make('home');
	}

}
