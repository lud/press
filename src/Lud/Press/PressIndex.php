<?php namespace Lud\Press;

/**
 * Every time we instanciate the index (once per request with default Laravel
 * IoC) the full directory structure is walked and every file is opened for
 * reading metadata. Caching is not handled by the index since we have many
 * possibilities of caching. This is left to do by the user.
 */

use Symfony\Component\Finder\Finder;
use View;

class PressIndex {

	private $indexCache = null;
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
		if (null !== $this->indexCache) {
			return $this->indexCache;
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
		$metas = array_map(function($file){
			return with(new PressFile($file->getPathname()))
				->parseMeta()
				;
		}, iterator_to_array($finder));
		// we want the keys to be relative to the base path
		$result = [];
		foreach ($metas as $key => $fileMeta) {
			$result[$fileMeta->id] = $fileMeta;
			$maxMTime = max($maxMTime, $fileMeta->mtime);
		}
		$this->indexCache = new Collection($result);
		$this->maxMTime = $maxMTime;
		return $this->indexCache;
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

