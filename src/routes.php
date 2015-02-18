<?php

use Lud\Press\PressPaginator;

$p = PressPaginator::PAGE_NAME;

Route::group(['pressCache' => true, 'namespace' => 'Lud\Press'], function($router) use($p){
	Route::get('/', ['uses' => 'PressPubController@home', 'as' => 'press.home']);
	// canonical for first page of all articles (home list)
	Route::get("/$p/1", function(){return Redirect::route('press.home');});
	Route::get("/$p/{page}", ['uses' => 'PressPubController@home', 'as' => 'press.home.page'])
		->where('page', '[0-9]+')
		;
	Route::get('article/{year}/{month}/{day}/{slug}', 'PressPubController@publish')
		->where(['year' => '[0-9]{4}','month' => '[0-9]{2}','day' => '[0-9]{2}'])
		;
	Route::get('page/{slug}', 'PressPubController@publish');
	Route::get('tag/{tag}', ['query' => 'tag|sort|page:3', 'uses' => 'PressPubController@loop', 'as' => 'press.tag']);
	Route::get("tag/{tag}/$p/1",  function($tag){return Redirect::route('press.tag',[$tag]); });
	Route::get("tag/{tag}/$p/{page}", ['uses' => 'PressPubController@tag', 'as' => 'press.tag.page'])
		->where('page', '[0-9]+')
		;
});

Route::group(['namespace' => 'Lud\Press'], function($router) use ($p) {
	Route::get('stop-editing', ['uses' => 'PressEditorController@stopEditing', 'as' => 'press.stop_editing']);
});

Route::group(['middleware' => 'auth', 'namespace' => 'Lud\Press'], function($router) use ($p) {
	Route::get('editing', ['uses' => 'PressEditorController@startEditing', 'as' => 'press.editing']);
	Route::get('refresh', ['uses' => 'PressEditorController@refresh', 'as' => 'press.refresh_page_cache']);
	Route::get('purge', ['uses' => 'PressEditorController@purge', 'as' => 'press.purge_cache']);
});

