<?php
return [
	'base_dir' => $_ENV['NOVEL_STORAGE_PATH'],
	'onFileMissing' => function() { return false; },
	'meta_sep' => '****',
	'url_fun' => function($fn,$meta) {

		throw new Exception("Osef de l'extension, faire les regex classic et simple, sans l'extension", 1);

		return URL::to(Novel::filenameTransform($fn,$meta,[
			'classic.sk' => ":year/:month/:day/:slug",
			'simple.sk' => ":slug",
		]));
	},
	'skriv' => [
		'urlProcessFunction' => function($url, $label, $targetBlank, $nofollow){
			if (starts_with($url,"novel:")) {
				$url = Novel::findFile([
					'filename'=> substr($url, strlen('novel:')),
					'onFileMissing' => function($fn) { throw new Exception("File $fn missing"); },
				])->url();
			}
			return [$url,$label,$targetBlank,$nofollow];
		},
		'softLinebreaks' => true,
		// 'softLinebreaks' => false,
	],
	'index_cache_minutes' => 1,
];
