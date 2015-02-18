<?php namespace Lud\Press;

use Config;
use Cookie;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Input;
use Redirect;
use URL;
use View;

class PressEditorController extends BaseController {

	public function startEditing()
	{
		return $this->redirectBack()->withCookie(Cookie::forever('pressEditing',true));
	}

	public function stopEditing()
	{
		return $this->redirectBack()->withCookie(Cookie::forget('pressEditing'));
	}

	public function refresh()
	{
		$path = Input::get('key');
		PressFacade::cache()->forget($path);
		try {
			$fileID = PressFacade::UrlToID($path);
			// now redirect to the file corresponding to the key
			$document = PressFacade::findFile($fileID);
			$url = $document->url() . '?' . microtime(1);
			return Redirect::to($url,302);
		} catch (\Exception $e) {
			// it's just not a file but a collection page
			return $this->redirectBack();
		}
	}

	public function purge()
	{
		PressFacade::cache()->flush();
		return $this->redirectBack();
	}

	private function redirectBack() {
		// we add a fake parameter to force reload
		$back = URL::to(Input::get('redir','/').'?'.microtime(1));
		return Redirect::to($back);
	}

}
