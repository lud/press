<?php namespace Lud\Press;

use Config;
use Cookie;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Press;
use Redirect;

class PressController extends BaseController {

	// use ValidatesRequests;

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function publish(Request $req, $truc)
	{
		// First we need to read the URL path. Then we match it with the url_map
		// in press conf
		$id = \Press::UrlToID($req->path());
		try {
			$document = \Press::findFile($id);
			$layout = $document->meta()->get('layout','default');
			return \View::make($layout)
				->with('meta',$document->meta())
				->with('cacheInfo',Press::editingCacheInfo())
				->with('content',$document->content());
		} catch (\Lud\Press\FileNotFoundException $e) {
			echo $e->getMessage(), "<br/>", PHP_EOL;
			dd("abort 404 " . __FILE__ . ':' . __LINE__);
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
		Press::cache()->forget($key);
		return Redirect::back();
	}

	public function purge()
	{
		Press::cache()->flush();
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

		$page_size = Press::getConf('default_page_size');
		$articles = Press::all();
		$pageArticles = $articles->forPage($page,$page_size);
		if (0 === $pageArticles->count() && $page !== 1) {
			return \Redirect::route('home');
		}
		// dd(with(new \Paginator)->resolveFacadeInstance());
		$paginator = new LengthAwarePaginator($articles,$articles->count(),2);
		return \View::make(Config::get('press::theme').'::home')
			->with('articles',$pageArticles)
			->with('cacheInfo',Press::editingCacheInfo())
			->with('paginator',$paginator);
	}


}
