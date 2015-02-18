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

/**
 * Find articles on the index
 * @param  string $query  a key/value list like "key:v1|key2:v2"
 * @param  array  $params an array to pick values from where values in $query are '_'
 * @return any A Press Collection of articles or the result of a reduce query
 */
	public function query($query, array $params=array()) {
		$collection = $this->all();
		$steps = $this->parseQuery($query, $this->mergeDefaults($params));
		foreach ($steps as $fun) {
			$collection = $fun($collection);
		}
		return $collection;
	}

	public function reBuild() {
		return $this->build(true);
	}

	protected function build($forceRebuild=false) {
		if (null !== $this->ramCache && !$forceRebuild) {
			return $this->ramCache;
		}
		$maxMTime = 0;
		$finder = new Finder();
		$extensionsRegex = $this->extensionsToRegex($this->getConf('extensions'));
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
		if ($lastMaxMTime >= $maxMTime && !$forceRebuild) {
			$cached = Cache::get(self::CACHE_KEY_BUILD);
			if ($cached instanceof COllection) return $cached;
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

	private function extensionsToRegex(array $extensions) {
		// we accept extensions with or without a dot before
		$dotPrefix = function($string) { return '\\.' . ltrim($string,'.'); };
		$prefixed = array_map($dotPrefix, $extensions);
		return '/(' . implode('|',$prefixed) . ')$/';
	}

	private function parseQuery($query, array $defaults) {
		$filters = [];
		foreach (explode('|',$query) as $part) {
			// the value is not parsed
			$parsed = explode(':',$part);
			$fun = $parsed[0];
			$default = isset($defaults[$fun]) ? $defaults[$fun] : null;
			$value = isset($parsed[1]) ? $parsed[1] : $default;
			$filters[] = self::makeReduce($fun, $value);
		}
		return $filters;
	}

	private function makeReduce($name,$value) {
		switch ($name) {
			case 'tag':
			case 'tags':
				// we accept multiple tags separated by commas
				return function(Collection $collection) use ($value) {
					return $collection->where('tags', explode(',',$value));
				};
			case 'lang':
				// we accept multiple langs separated by commas
				return function(Collection $collection) use ($value) {
					return $collection->where('lang', explode(',',$value));
				};
			case 'sort':
				return function(Collection $collection) use ($value) {
					// add "desc" as a default which will be bound in $direction
					// if no direction is specified
					list($field,$direction) = explode(',',"$value,desc");
					$sortBy = $this->getSortFun($field,$direction==='desc');
					return $collection->sort($sortBy);
				};
			case 'page':
			case 'count':
				return function(Collection $collection) {
					return $collection->count();
				};
			default:
				throw new \Exception("Unknown PressIndex reduce $name");
		}
	}

	private function mergeDefaults($params) {
		static $defaults = [
			'sort' => 'date,desc'
		];
		return array_merge($defaults,$params);
	}

	/**
	 * gives a fun to sort metawrappers on a field
	 * @param  string $fieldName meta entry name
	 * @param  bool $desc        if we sort descending
	 * @return integer           -1 | 0 | 1
	 */
	private function getSortFun($fieldName,$desc) {

		$m = $desc ? -1 : 1; // sort modifier

		return function(MetaWrapper $fileA, MetaWrapper $fileB)
			use ($fieldName,$m) {
				$vA = $fileA->get($fieldName);
				$vB = $fileB->get($fieldName);
				if ($vA == $vB) return 0;
				else return $vA < $vB ? -1*$m : 1*$m ;
			};
	}

}

