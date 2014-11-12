<?php
return [
	'base_dir' => $_ENV['NOVEL_STORAGE_PATH'],
	'skriv' => [
		'urlProcessFunction' => function($url, $label, $targetBlank, $nofollow){
			if (starts_with($url,"novel:")) {
				$url = Novel::findFile(substr($url, strlen('novel:')))->url();
			}
			return [$url,$label,$targetBlank,$nofollow];
		},
		'softLinebreaks' => true,
		// 'softLinebreaks' => false,
	],
	'default_page_size' => 2,
];
