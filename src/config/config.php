<?php
return [
	'base_dir' => env('PRESS_STORAGE_PATH','/tmp'),
	'meta_sep' => '****',
	'brand' => '(conf :: press.brand)',
	'url_map' => [
		'classic' => "article/:year/:month/:day/:slug",
		'simple'  => "page/:slug",
	],
	'storage_path' => base_path('public/.press-cache'),
	'filename_schemas' => ['classic','simple'],
	'extensions' => ['.sk','md','htm','.html'],
	'default_page_size' => 10,
	'theme' => env('PRESS_THEME','press'),
	'load_themes' => [],
	'skriv' => [
		'urlProcessFunction' => function($url, $label, $targetBlank, $nofollow){
			$url = \Lud\Press\PressHTMLTransformer::maybeTransformHref($url);
			return [$url,$label,$targetBlank,$nofollow];
		},
		'softLinebreaks' => true,
	],
];

