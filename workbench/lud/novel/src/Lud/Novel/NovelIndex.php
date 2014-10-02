<?php namespace Lud\Novel;

use View;
use Cache;
use Symfony\Component\Finder\Finder;

class NovelIndex {

	protected $app;

	public function __construct($app,$conf=[]) {
		$this->app = $app;
	}

	public function all() {
		return $this->getCache('all');
	}

	public function subDir($path) {
		$path = trim($path,'/');
		return $this->getCache('dir:'.$path);
	}

	public function getCache($key) {
		$minutes = \Config::get('novel::config.index_cache_minutes',1);

		if (intval($minutes) === 0) {
			return $this->build($key);
		}
		return Cache::remember("novel.$key",$minutes, function() use ($key) {
			return $this->build($key);
		});
	}

	protected function build($key) {
		// we want ALL the files
		if ($key === 'all') {
			return $this->fullTreeMeta();
		}
		// we want a subdirectory (recursive)
		else if (starts_with($key,'dir:')) {
			$path = substr($key, strlen('dir:'));
			return $this->subDirectoryTree($path);
		}
	}

	protected function fullTreeMeta() {
		$finder = new Finder();
		$extensionsRegex = static::extensionsToRegex($this->getConf('extensions'));
		$sorWithoutPath = function(\SplFileInfo $a, \SplFileInfo $b) {
			return strcmp(pathinfo($a->getFilename(),PATHINFO_FILENAME), pathinfo($b->getFilename(),PATHINFO_FILENAME));
		};
		$finder->files()
			->in($this->getConf('base_dir'))
			->sort($sorWithoutPath)
			->name($extensionsRegex)
		;
		$metas = array_map(function($file){
			return with(new NovelFile($this->app, $file->getPathname()))
				->parseMeta()
				->all()
				;
		}, iterator_to_array($finder));
		// we want the keys to be relative to the base path
		$result = [];
		foreach ($metas as $key => $fileMeta) {
			$result[$fileMeta['rel_path']] = $fileMeta;
		}
		return $result;
	}

	protected function subDirectoryTree($path) {
		$path = "$path/"; // add a final slash to match only directories
		$all = $this->fullTreeMeta();
		$result = [];
		foreach ($all as $key => $meta) {
			if (starts_with($meta['rel_path'],$path)) {
				$result[$key] = $meta;
			}
		}
		return $result;
	}

	protected function getConf($key=null,$default=null) {
		return $this->app['novel']->getConf($key,$default);
	}

	static function extensionsToRegex(array $extensions) {
		// we accept extensions with or without a dot before
		$dotPrefix = function($string) { return '\\.' . ltrim($string,'.'); };
		$prefixed = array_map($dotPrefix, $extensions);
		return '/(' . implode('|',$prefixed) . ')$/';
	}

}

