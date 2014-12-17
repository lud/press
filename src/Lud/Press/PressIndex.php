<?php namespace Lud\Press;

/**
 * Every time we instanciate the index (once per request with default Laravel
 * IoC) the full directory structure is walked and every file is opened for
 * reading metadata. Caching is not handled by the index since we have many
 * possibilities of caching. This is left to do by the user.
 */

use Symfony\Component\Finder\Finder;
use View;
use Cache;

class PressIndex {

	const CACHE_KEY_BUILD = 'PressIndexBuild';
	const CACHE_KEY_MAXMTIME = 'PressIndexMaxMTime';

	private $ramCache = null;
	private $maxMTime = 0;

	public function all() {
		return $this->build();
	}

	public function count() {
		return $this->build()->count();
	}

	public function query($key) {
		$collection = $this->all();
		$steps = $this->parseQuery($key);
		foreach ($steps as $f) {
			$collection = $f($collection);
		}
		return $collection;
	}

	protected function build() {
		if (null !== $this->ramCache) {
			return $this->ramCache;
		}
		$maxMTime = 0;
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
		// We look for the maximum mtime of the files
		$filesArray = iterator_to_array($finder);
		foreach ($filesArray as $file) {
			$maxMTime = max($maxMTime, max($file->getMTime(),$file->getCTime()));
		}
		$this->maxMTime = $maxMTime;
		// now we get the cache for the last maxMTime calculated, with
		// 0 as a default
		$lastMaxMTime = Cache::get(self::CACHE_KEY_MAXMTIME,0);
		// Now, if the new maxMtime is higher than the cached one,
		// files were modified since the last build. So we need to
		// build. But if the cached is the same (or superior, why ?),
		// we return from the cache
		if ($lastMaxMTime >= $maxMTime) {
			return Cache::get(self::CACHE_KEY_BUILD);
		}
		//---------------------------------------------------

		// Here we put the new cache time

		Cache::forever(self::CACHE_KEY_MAXMTIME, $maxMTime);

		$metas = array_map(function($file){
			return with(new PressFile($file->getPathname()))
				->parseMeta()
				;
		}, iterator_to_array($finder));
		// we want the keys to be relative to the base path
		$result = [];
		foreach ($metas as $key => $fileMeta) {
			$result[$fileMeta->id] = $fileMeta;

		}
		$this->ramCache = new Collection($result);
		Cache::forever(self::CACHE_KEY_BUILD, $this->ramCache);
		return $this->ramCache;
	}

	public function getFile($id) {
		$collection = $this->all();
		if ($collection->has($id)) {
			$meta = $collection->get($id);
			$filename = $meta->filename;
			return new PressFile($filename,$meta);
		}
		throw new FileNotFoundException("Cannot find file id=$id");
	}

	public function getModTime() {
		return $this->maxMTime;
	}

	protected function getConf($key=null,$default=null) {
		return PressFacade::getConf($key,$default);
	}

	static function extensionsToRegex(array $extensions) {
		// we accept extensions with or without a dot before
		$dotPrefix = function($string) { return '\\.' . ltrim($string,'.'); };
		$prefixed = array_map($dotPrefix, $extensions);
		return '/(' . implode('|',$prefixed) . ')$/';
	}

	static function parseQuery($query) {
		$filters = [];
		foreach (explode('|',$query) as $part) {
			$args = explode(':',$part);
			$fun = array_shift($args);
			$filters[] = self::makeReduce($fun,$args);
		}
		return $filters;
	}

	static function makeReduce($name,$args) {
		switch ($name) {
			case 'tags':
			case 'tag':
				return function($collection) use ($args) { return $collection->where('tags', explode(',',$args[0])); };
			case 'lang':
				return function($collection) use ($args) { return $collection->where('lang', explode(',',$args[0])); };
			case 'count':
				return function($collection) {
					return $collection->count();
				};
			default:
				throw new \Exception("Unknown PressIndex reduce $name");
		}
	}

}

