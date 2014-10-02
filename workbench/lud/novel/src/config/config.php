<?php
return [
	'base_dir' => '/tmp',
	'onFileMissing' => function() { return false; },
	'meta_sep' => '****',
	'url_fun' => function($filename) {
		throw new Exception ('Please define novel::config.url_fun in configuration');
	},
	'filename_schemas' => ['classic.sk','classic.md','simple.sk','simple.md'],
	'extensions' => ['.sk','md'],
	'index_cache_minutes' => 10,
	'default_page_size' => 10,
];

