<?php
return [
	'base_dir' => getenv('PRESS_STORAGE_PATH') ?: '/tmp',
	'meta_sep' => '****',
	'url_map' => [
		'classic' => "article/:year/:month/:day/:slug",
		'simple'  => "page/:slug",
	],
	'storage_path' => storage_path().'/app/press-cache',
	'filename_schemas' => ['classic','simple'],
	'extensions' => ['.sk','md','htm','.html'],
	'default_page_size' => 10,
	'theme' => getenv('PRESS_THEME') ?: 'press',
	'skriv' => [
		'urlProcessFunction' => function($url, $label, $targetBlank, $nofollow){
			$url = \Lud\Press\PressHTMLTransformer::maybeTransformHref($url);
			return [$url,$label,$targetBlank,$nofollow];
		},
		'softLinebreaks' => true,
	],
];

