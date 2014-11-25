<?php
return [
	'base_dir' => $_ENV['PRESS_STORAGE_PATH'],
	'default_page_size' => 2,
	'skriv' => [
		'urlProcessFunction' => function($url, $label, $targetBlank, $nofollow){
			$url = \Lud\Press\PressHTMLTransformer::maybeTransformHref($url);
			return [$url,$label,$targetBlank,$nofollow];
		},
		'softLinebreaks' => true,
	],
	'__theme' => 'lpdp-theme',
	'themes_dirs' => [
		'lpdp-theme' => base_path('resources/lpdp-theme'),
	],
];
