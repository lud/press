<?php
return [
	'base_dir' => getenv('PRESS_STORAGE_PATH') ?: '/tmp',
	'meta_sep' => '****',
	'url_map' => [
		'classic' => "article/:year/:month/:day/:slug",
		'simple'  => "page/:slug",
	],
	'filename_schemas' => ['classic','simple'],
	'extensions' => ['.sk','md','htm','.html'],
	'default_page_size' => 10,
	'theme' => 'press'
];

