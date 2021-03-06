<?php namespace Lud\Press;

use Cache;
use Closure;
use Cookie;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use zz\Html\HTMLMinify;

class PressHttpCache implements Middleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // @todo check if not authentified instead of editing
        if ($request->cookie('pressEditing')) {
            PressFacade::setEditing();
            return $next($request);
        }

        // The cache is automatically served by the webserver. So if
        // we hit this code, the only thing to do is to save the
        // response in an .html file

        // the generated output MUST NOT have user-based content. It
        // must be the same content for everybody

        // PressFacade::skipCache() can be used to not cache the
        // current request

        $response = $next($request);
        if (!PressFacade::isCacheableRequest($request, $response)) {
            return $response;
        }
        $contentHTML = $response->getContent();
        $miniContent = HTMLMinify::minify($contentHTML, [
            'doctype' => HTMLMinify::DOCTYPE_HTML5,
            ]);
        $cache = PressFacade::cache();
        $cache->writeFile($miniContent);
        return $this->makeFakeResponse($miniContent);
    }

    private function makeFakeResponse($content)
    {
        $response = new Response();
        $response->setContent($content);
        return $response;
    }
}
