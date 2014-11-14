<?php namespace Lud\Novel;

use View;
use Symfony\Component\Finder\Finder;

class NovelService {

	const FILE_PATH_TYPE = 1;
	const URL_PATH_TYPE = 2;

	protected $conf = [];
	protected $app;
	protected $editing = false;
	protected $currentEditingCacheInfo;

	public function __construct($app,$conf=[]) {
		$this->configure($conf);
		$this->app = $app;
	}

	public function findFile($filename) {
		return $this->index()->getFile($filename);
	}

	public function configure($values) {
		$sysValues = array_except($values,['url','filename']);
		$this->conf = array_merge($this->conf,$sysValues);
		return array_merge($this->conf,$values);
	}

	public function getConf($key=null,$default=null) {
		if (null === $key) return $this->conf;
		elseif (isset($this->conf[$key])) return $this->conf[$key];
		else return $default;
	}

	static public function filenameJoin($parts) {
		return implode(DIRECTORY_SEPARATOR,$parts);
	}

	public function pathInfo($fn,$schema,$type=self::FILE_PATH_TYPE) {
		// pre($fn,"path to match");
		if (self::FILE_PATH_TYPE === $type) {
			// Work only on the basename
			$fn = pathinfo($fn,PATHINFO_BASENAME);
			$extensions = array_map(function($ext){ return ltrim($ext,'.'); }, $this->getConf('extensions'));
			$extensionsRe = '\\.(' . implode('|',$extensions) . ')';
		}
		elseif (self::URL_PATH_TYPE === $type) {
			// this is an URL, evaluate the full path
			$extensionsRe = '';
		}
		$pattern = static::filePathSchemaToRegex($schema,$extensionsRe);

		$matches = [];
		if (preg_match($pattern,$fn,$matches)) {
			// we filter out the numeric keys of matches
			$keys = array_keys($matches);
			$numeric_keys = array_filter($keys,'is_numeric');
			// pre("$pattern match $fn");
			return array_except($matches, $numeric_keys);
		// } else {
		//	pre("$pattern NO match $fn");
		}
		return false;
	}

	public function filenameToUrl($meta) {
		$schemas = $this->getConf('url_map');
		foreach ($schemas as $pathSchema => $urlSchema) {
			if ($props = $this->pathInfo($meta->filename,$pathSchema,self::FILE_PATH_TYPE)) {
				return \URL::to(static::replaceStrParts($urlSchema,array_merge($props,$meta->all())));
			}
		}
		throw new \Exception('Cannot transform filename "'.$meta->filename.'"');
	}

	// the schemas must return an ID, i.e. a file's name without the directory
	// and without an extension
	public function UrlToID($urlPath) {
		$schemas = array_flip($this->getConf('url_map'));
		foreach ($schemas as $pathSchema => $urlSchema) {
			if ($props = $this->pathInfo($urlPath,$pathSchema,self::URL_PATH_TYPE)) {
				return static::replaceStrParts(static::expandFilePathSchema($urlSchema),$props);
			}
		}
		throw new \Exception("Cannot transform URL '$urlPath'");
	}

	static function replaceStrParts($schema,$values) {
		// @todo this is ugly, need to find the corresponding library or at
		// least use regexes
		$keysFound = [];
		$matches = [];
		if (preg_match_all('/:[a-zA-Z0-9_]+/', $schema, $matches)) {
			foreach($matches[0] as $matchKey) {
				$key = substr($matchKey,1); // drop the colon
				if (isset($values[$key])) {
					$schema = str_replace($matchKey, $values[$key], $schema);
				}
			}
		}
		return $schema;
	}

	static function filePathSchemaToRegex($schema,$append) {
		switch($schema) {
			case 'classic':
				$pattern = "@(?P<year>[0-9]{4})-(?P<month>[0-9]{2})-(?P<day>[0-9]{2})-(?P<slug>.+)$append$@";
				break;
			case 'simple':
				$pattern = "@(?P<slug>.+)$append$@";
				break;
			default: // Custom schema
				$schemaToRegex = [
					':year' 	=> '(?P<year>[0-9]{4})',
					':month'	=> '(?P<month>[0-9]{2})',
					':day'  	=> '(?P<day>[0-9]{2})',
					':slug' 	=> '(?P<slug>.+)',
				];
				$scParts = array_keys($schemaToRegex);
				$reParts = array_values($schemaToRegex);
				$pattern = '@' . str_replace($scParts,$reParts,$schema) . "$append$@";
		}
		return $pattern;
	}

	static function expandFilePathSchema($schema) {
		switch ($schema) {
			case 'classic': return ':year-:month-:day-:slug';
			case 'simple': return ':slug';
			default: return $schema;
		}
	}

	public function cache() {
		return $this->app['novel.cache'];
	}

	public function index() {
		return $this->app['novel.index'];
	}

	public function query($query) {
		return $this->index()->query($query);
	}

	public function all() {
		return $this->index()->all();
	}

	// Cache & Editing ------------------------------------------------------

	public function isEditing() {
		return $this->editing;
	}

	public function setEditing() {
		$this->editing = true;
	}

	public function editingCacheInfo() {
		if (!$this->isEditing()) return null;
		$info = $this->cache()->current();
		unset($info->content);
		$info->indexMTime = $this->index()->getModTime();
		$info->isCacheStale = $info->indexMTime > $info->cacheTime;
		return $info;
	}


}

