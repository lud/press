<?php namespace Lud\Novel;

use Cache;
use Symfony\Component\Finder\Finder;
use View;

class NovelIndex {

	public function all() {
		return $this->cache('all', function() {
			return $this->fullTree();
		});
	}

	public function query($key) {
		return $this->cache($key, function() use ($key) {
			$collection = $this->all(); // here use fullTree to use a fresh version, but takes more time
			$steps = $this->parseQuery($key);
			foreach ($steps as $f) {
				$collection = $f($collection);
			}
			return $collection;
		});
	}

	public function subDir($path) {
		$path = trim($path,'/');
		return $this->getCache('dir:'.$path)->all();
	}

	public function cache($key,$f) {
		$minutes = \Config::get('novel::config.index_cache_minutes', 1);
		if (intval($minutes) === 0)
			return $f();
		else
			return Cache::remember("novel.$key", $minutes, $f);
	}

	protected function fullTree() {
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
			return with(new NovelFile($file->getPathname()))
				->parseMeta()
				;
		}, iterator_to_array($finder));
		// we want the keys to be relative to the base path
		$result = [];
		foreach ($metas as $key => $fileMeta) {
			$result[$fileMeta->rel_path] = $fileMeta;
		}
		return new Collection($result);
	}

	protected function subDirectoryTree($path) {
		$path = "$path/"; // add a final slash to match only directories
		$all = $this->fullTree();
		$result = [];
		foreach ($all as $key => $meta) {
			if (starts_with($meta['rel_path'],$path)) {
				$result[$key] = $meta;
			}
		}
		return $result;
	}

	protected function getConf($key=null,$default=null) {
		return NovelFacade::getConf($key,$default);
	}

	static function extensionsToRegex(array $extensions) {
		// we accept extensions with or without a dot before
		$dotPrefix = function($string) { return '\\.' . ltrim($string,'.'); };
		$prefixed = array_map($dotPrefix, $extensions);
		return '/(' . implode('|',$prefixed) . ')$/';
	}

	static function parseQuery($query) {
		$filters = [];
		foreach (explode('.',$query) as $part) {
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
			case 'page':
				return function($collection) use ($args) {
					$page = intval($args[0]) - 1;
					$pageSize = intval(isset($args[1]) ? $args[1] : \Config::get('novel::config.default_page_size'));
					$drop = $page * $pageSize;
					return $collection->drop($drop)->take($pageSize);
				};
			case 'count':
				return function($collection) {
					return $collection->count();
				};
			default:
				throw new \Exception("Unknown Novel reduce $name");
		}
	}

}

