<?php
return [
	'base_dir' => $_ENV['PRESS_STORAGE_PATH'],
	'skriv' => [
		'urlProcessFunction' => function($url, $label, $targetBlank, $nofollow){
			if (starts_with($url,"press:")) {
				$url = Press::findFile(substr($url, strlen('press:')))->url();
			}
			return [$url,$label,$targetBlank,$nofollow];
		},
		'softLinebreaks' => true,
		// 'softLinebreaks' => false,
	],
	'default_page_size' => 2,
];
