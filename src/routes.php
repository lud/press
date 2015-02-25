<?php namespace Lud\Press;

use Route;

Route::group(['namespace' => 'Lud\Press'], function($router) {
	Route::get('stop-editing', ['uses' => 'PressEditorController@stopEditing', 'as' => 'press.stop_editing']);
});

Route::group(['middleware' => 'auth', 'namespace' => 'Lud\Press'], function($router) {
	Route::get('editing', ['uses' => 'PressEditorController@startEditing', 'as' => 'press.editing']);
	Route::get('refresh', ['uses' => 'PressEditorController@refresh', 'as' => 'press.refresh_page_cache']);
	Route::get('purge', ['uses' => 'PressEditorController@purge', 'as' => 'press.purge_cache']);
});

// We will setup a route for each URL schema defined in the url_map config. We
// do not care about file schemas, since once we hit the controller, it handles
// the file search from URL
foreach (PressFacade::getConf('url_map') as $urlSchema) {
	$routeURL = PressService::replaceStrParts($urlSchema,function($key){
		return '{'.$key.'}';
	});
	Route::get($routeURL, ['pressCache' => true, 'uses' => 'Lud\Press\PressPubController@publish']);
}
// exit;
