<?php namespace Lud\Press;

use Config;
use Cookie;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
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
		try {
			// First we need to read the URL path. Then we match it with the url_map
			// in press conf
			$id = PressFacade::UrlToID($req->path());
			$document = PressFacade::findFile($id);
			// if we are not at the exact document URL, we redirect
			if (\URL::to($req->path()) !== $document->url()) {
				return Redirect::to($document->url(),301);
			}
			$layout = $document->meta()->get('layout','default');
			return \View::make($layout)
				->with('meta',$document->meta())
				->with('cacheInfo',PressFacade::editingCacheInfo())
				->with('themeAssets',PressFacade::getThemeAssets($document->meta()->theme))
				->with('content',$document->content());
		} catch (FileNotFoundException $e) {
			abort(404);
		}
	}

	public function startEditing()
	{
		return Redirect::back()->withCookie(Cookie::forever('pressEditing',true));
	}

	public function stopEditing()
	{
		return Redirect::back()->withCookie(Cookie::forget('pressEditing'));
	}

	public function refresh($key='')
	{
		PressFacade::cache()->forget($key);
		return Redirect::back();
	}

	public function purge()
	{
		PressFacade::cache()->flush();
		return Redirect::back();
	}

	public function home($page = 1)
	{
		$page = max($page,1); //set the page to minimum 1
		$view = PressFacade::getConf('theme').'::home';
		$all = PressFacade::all()->sort(Collection::byDateDesc());
		return $this->displayCollection($all,$page,$view);
	}

	public function tag($tag, $page=1)
	{
		$page = max($page,1); //set the page to minimum 1
		$view = PressFacade::getConf('theme').'::tag';
		$found = PressFacade::query("tags:$tag");
		// If no posts were found, we do not cache the query. Without this,
		// anyone could fill the cache with requests to /tag/a, tag/aa, tag/aaa,
		// etc..
		if (! $found->count()) PressFacade::skipCache();
		$pathBase = \URL::route('press.tag',[$tag]);
		return $this->displayCollection($found,$page,$view,$pathBase);
	}

	protected function displayCollection($articles, $page, $view, $baseUrl=null) {
		$articles->sort(Collection::byDateDesc());
		$page_size = PressFacade::getConf('default_page_size');
		$pageArticles = $articles->forPage($page,$page_size);
		// if we have no articles for this page and page is not the first page,
		// let's go to the home. Should go 404 ?
		if (0 === $pageArticles->count() && $page !== 1) {
			return abort(404);
		}
		$paginator = $articles->getPaginator($page_size);
		if (null !== $baseUrl) $paginator->setBasePath($baseUrl);
		return \View::make($view)
			->with('articles',$pageArticles)
			->with('cacheInfo',PressFacade::editingCacheInfo())
			->with('themeAssets',PressFacade::getDefaultThemeAssets())
			->with('paginator',$paginator);
	}

}
