<?php namespace App\Http\Middleware;

use Cache;
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
		$path = $request->getPathInfo();
		// the query string is normalized, so changing the order of the params
		// still hits the same cache
		$qs = $request->getQueryString();
		$cacheKey = "$path?$qs";

		// We check the cache first, because to access route infos, the request
		// must proceed through the stack. So, if caching is unset on a route,
		// the cache must be "manually" deleted

		if (Cache::has($cacheKey)) {
			return $this->makeFakeResponse(Cache::get($cacheKey));
		}

		// proceed with the stack if the response is not cached.
		$response = $next($request);
		$routeOpts = $request->route()->getAction();

		if (!isset($routeOpts['pressCache']) || !$routeOpts['pressCache'] == true) {
			return $response;
		}

		// we only cache 200 responses
		if (200 !== $response->getStatusCode()) {
			return $response;
		}

		$content = $response->getContent();

		Cache::forever($cacheKey,$content);

		return $this->makeFakeResponse($content);

	}

	private function makeFakeResponse($content) {
		$response = new Response();
		$response->setContent($content);
		return $response;
	}

}
