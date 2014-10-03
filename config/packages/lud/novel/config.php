<?php
return [
	'base_dir' => $_ENV['NOVEL_STORAGE_PATH'],
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
	'index_cache_minutes' => 0,
];
