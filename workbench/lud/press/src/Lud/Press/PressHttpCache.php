<?php namespace Lud\Press;

use Cache;
use Cookie;
use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;

class PressHttpCache implements Middleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{

		$pressCache = PressFacade::cache();

		if ($request->cookie('pressEditing')) {
			PressFacade::setEditing();
			return $next($request);
		}
		// We check the cache before checking if the route must be cached
		// because to access route infos, the request must proceed through the
		// stack. So, if caching is unset on a route, the cache must be
		// "manually" deleted

		if ($pressCache->hasCurrentRequest()) {
			return $this->makeFakeResponse($pressCache->getCurrentRequest());
		}
		// proceed with the stack if the response is not cached.
		$response = $next($request);
		$routeOpts = $request->route()->getAction();
		// we only cache 200 responses that have option pressCache set to truthy
		if (!isset($routeOpts['pressCache'])
			|| !$routeOpts['pressCache'] == true
			|| 200 !== $response->getStatusCode())
		{
			return $response;
		}
		$cache = $pressCache->setCurrentRequestCacheContent($response->getContent());
		return $this->makeFakeResponse($cache);
	}

	private function makeFakeResponse($cache) {
		$response = new Response();
		$response->setContent($cache->content);
		return $response;
	}



}
