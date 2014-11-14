<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cookie;
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


}
