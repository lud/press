<?php
// LPDP theme file
return [
	'styles' => [
		asset('packages/lud/press/lib/css/bootstrap.min.css'),
		asset('lpdp/css/lpdp.css'),
	],
	'scripts' => [
		'//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js',
		asset('packages/lud/press/lib/js/bootstrap.min.js'),
	],
	'hookBeforeContent' => [
		'press::pressParts.navbar',
	],
];
