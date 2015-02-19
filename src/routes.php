<?php

Route::group(['namespace' => 'Lud\Press'], function($router) {
	Route::get('stop-editing', ['uses' => 'PressEditorController@stopEditing', 'as' => 'press.stop_editing']);
});

Route::group(['middleware' => 'auth', 'namespace' => 'Lud\Press'], function($router) {
	Route::get('editing', ['uses' => 'PressEditorController@startEditing', 'as' => 'press.editing']);
	Route::get('refresh', ['uses' => 'PressEditorController@refresh', 'as' => 'press.refresh_page_cache']);
	Route::get('purge', ['uses' => 'PressEditorController@purge', 'as' => 'press.purge_cache']);
});

