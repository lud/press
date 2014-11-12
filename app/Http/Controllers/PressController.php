<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
				->with('content',$document->content());
		} catch (\Lud\Novel\FileNotFoundException $e) {
			abort(404);
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
