<?php


Route::group(['pressCache' => true, 'namespace' => 'Lud\Press'], function($router) use($_SET_HOME_ROUTE){
	if ($_SET_HOME_ROUTE) {
		Route::get('/', ['uses' => 'PressController@home', 'as' => 'press.home']);
		// canonical for first page of all articles (home list)
		Route::get('/p/1', function(){return Redirect::route('press.home');});
		Route::get('/p/{page}', ['uses' => 'PressController@home', 'as' => 'press.home.page'])
			->where('page', '[0-9]+')
			;
	}
	Route::get('article/{year}/{month}/{day}/{slug}', 'PressController@publish')
		// ->where(['year' => '[0-9]{4}','month' => '[0-9]{2}','day' => '[0-9]{2}'])
		;
	Route::get('page/{slug}', 'PressController@publish');
	Route::get('tag/{tag}', ['uses' => 'PressController@tag', 'as' => 'press.tag']);
	Route::get('tag/{tag}/p/1',  function($tag){return Redirect::route('press.tag',[$tag]); });
	Route::get('tag/{tag}/p/{page}', ['uses' => 'PressController@tag', 'as' => 'press.tag.page'])
		->where('page', '[0-9]+')
		;
});

Route::group(['__middleware' => 'auth', 'namespace' => 'Lud\Press'], function($router){
	Route::get('editing', ['uses' => 'PressController@startEditing', 'as' => 'press.editing']);
	Route::get('stop-editing', ['uses' => 'PressController@stopEditing', 'as' => 'press.stop_editing']);
	Route::get('refresh', ['uses' => 'PressController@refresh', 'as' => 'press.refresh_page_cache']);
	Route::get('purge', ['uses' => 'PressController@purge', 'as' => 'press.purge_cache']);
});

