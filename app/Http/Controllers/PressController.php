<?php namespace App\Http\Controllers;

use Config;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Novel;
use Redirect;

class PressController extends Controller {

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function publish(Request $req, $truc)
	{
		// First we need to read the URL path. Then we match it with the url_map
		// in novel conf
		$id = \Novel::UrlToID($req->path());
		try {
			$document = \Novel::findFile($id);
			$layout = $document->meta()->get('layout','default');
			return \View::make($layout)
				->with('meta',$document->meta())
				->with('cacheInfo',Novel::editingCacheInfo())
				->with('content',$document->content());
		} catch (\Lud\Novel\FileNotFoundException $e) {
			abort(404);
		}
	}

	public function startEditing()
	{
		return Redirect::back()->withCookie(Cookie::forever('pressEditing',true));
	}

	public function stopEditing()
	{
		return Redirect::back()->withCookie(Cookie::forever('pressEditing',false));
	}

	public function refresh($key)
	{
		Novel::cache()->forget($key);
		return Redirect::back();
	}

	public function purge()
	{
		Novel::cache()->flush();
		return Redirect::back();
	}

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
