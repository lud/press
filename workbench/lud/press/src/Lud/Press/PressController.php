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
			$layout = $document->meta()->get('layout','default');
			return \View::make($layout)
				->with('meta',$document->meta())
				->with('cacheInfo',PressFacade::editingCacheInfo())
				->with('themeAssets',PressFacade::getThemeAssets($document->meta()->theme))
				->with('content',$document->content());
		} catch (BaseException $e) {
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
		$view = Config::get('press::theme').'::home';
		return $this->displayCollection(PressFacade::all(),$page,$view);
	}

	protected function displayCollection($articles, $page, $view, $tplData=[]) {
		$page_size = PressFacade::getConf('default_page_size');
		$pageArticles = $articles->forPage($page,$page_size);
		// if we have no articles for this page and page is not the first page,
		// let's go to the home. Should go 404 ?
		if (0 === $pageArticles->count() && $page !== 1) {
			return abort(404);
		}
		$paginator = $articles->getPaginator($page_size);
		return \View::make($view, $tplData)
			->with('articles',$pageArticles)
			->with('cacheInfo',PressFacade::editingCacheInfo())
			->with('themeAssets',PressFacade::getDefaultThemeAssets())
			->with('paginator',$paginator);
	}

}
