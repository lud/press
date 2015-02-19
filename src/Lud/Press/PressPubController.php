<?php namespace Lud\Press;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;


class PressPubController extends BaseController {

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
			$layout = $document->meta()->get('layout');
			return \View::make($layout)
				->with('meta',$document->meta())
				->with('cacheInfo',PressFacade::editingCacheInfo())
				->with('themeAssets',PressFacade::getThemeAssets($document->meta()->theme))
				->with('content',$document->content());
		} catch (FileNotFoundException $e) {
			abort(404);
		}
	}

	// @todo split the method in smaller
	public function showCollection(Route $route, Router $router) {

		// We extract the params not set in the query from the URL
		$queryParams = $route->parameters();
		// Figure out the page from the route URL parameters
		$page = max(1,$route->getParameter('page'));

		$routeParams = $route->getAction();
		$query = $routeParams['query'];

		$articles = PressFacade::query($query,$queryParams);

		// create a paginator if required
		if ($routeParams['paginate']) {
			$page_size = PressFacade::getConf('default_page_size');
			$paginator = $articles->getPaginator($page_size);
			$articles = $articles->forPage($page,$page_size);
		} else {
			$paginator = $articles->getPaginator(999999);
		}

		$view = PressFacade::getConf('theme')."::home";

		if (0 === $articles->count() && $page !== 1) {
			return abort(404);
		}

		// paginator base path
		$baseUrlParamNames = $this->getRouteParamNames($routeParams['base_route'], $router);
		$baseUrlParams = array_only($queryParams, $baseUrlParamNames);
		$basePath = \URL::route($routeParams['base_route'],$baseUrlParams);
		$paginator->setBasePath($basePath);

		return View::make($view)
			->with('articles', $articles)
			->with('cacheInfo',PressFacade::editingCacheInfo())
			->with('themeAssets',PressFacade::getDefaultThemeAssets())
			->with('paginator',$paginator);
	}

	private function getRouteParamNames($routeName, $router) {
		return $router
			->getRoutes()
			->getByName($routeName)
			->getCompiled()
			->getPathVariables();
	}

}
