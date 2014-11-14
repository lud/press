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
		$this->middleware('pressHttpCache');

		$page = \Input::get('page');
		if (null !== $page && $page < 2) {
			// if page IS set to 1 or 0 or inferior, redirect to the home
			return \Redirect::route('home',[],301);
		}
		$page = max($page,1); //set the page to minimum 1

		$page_size = Novel::getConf('default_page_size');
		$articles = Novel::all();
		$pageArticles = $articles->forPage($page,$page_size);
		if (0 === $pageArticles->count() && $page !== 1) {
			return \Redirect::route('home');
		}
		// dd(with(new \Paginator)->resolveFacadeInstance());
		$paginator = new LengthAwarePaginator($articles,$articles->count(),2);
		return \View::make('home')
			->with('articles',$pageArticles)
			->with('cacheInfo',Novel::editingCacheInfo())
			->with('paginator',$paginator);
	}

}
