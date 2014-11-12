<?php namespace App\Http\Controllers;

use Config;
use Novel;
use Illuminate\Pagination\LengthAwarePaginator;

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
	|	$router->get('/', 'HomeController@showWelcome');
	|
	*/

	public function index()
	{
		$page = \Input::get('page',1);
		$page_size = Novel::getConf('default_page_size');
		$articles = Novel::all();
		$pageArticles = $articles->forPage($page,$page_size);
		// dd(with(new \Paginator)->resolveFacadeInstance());
		$paginator = new LengthAwarePaginator($articles,$articles->count(),2);
		return \View::make('home')
			->with('articles',$pageArticles)
			->with('paginator',$paginator);
	}

}
