<?php namespace Lud\Press;

// @todo split object responsibilities

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Lud\Utils\ChainableGroup;
use Symfony\Component\Finder\Finder;

class PressService {

	const FILE_PATH_TYPE = 1;
	const URL_PATH_TYPE = 2;

	protected $conf = [];
	protected $app;
	protected $editing = false;
	protected $currentEditingCacheInfo;
	protected $mustCacheCurrentRequest = true;

	public function __construct($app,$conf=[]) {
		$this->configure($conf);
		$this->app = $app;
	}

	// findFile accepts fileID or filename. We check if the filename ends with
	// a known extension. If so, we remove it.
	public function findFile($filename) {
		$fileID = $this->filenameToId($filename);
		return $this->index()->getFile($fileID);
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

	public function filenameToId($fn) {
		$fn = pathinfo($fn,PATHINFO_BASENAME);
		$fnLen = strlen($fn);
		foreach ($this->getExtensions() as $ext) {
			$dotted = ".$ext";
			$extLen = strlen($dotted);
			if ($dotted === substr($fn, -$extLen)) {
				return substr($fn, 0, $fnLen - $extLen);
			}
		}
		return $fn;
	}

	public function getExtensions() {
		return array_map(function($ext){ return ltrim($ext,'.'); }, $this->getConf('extensions'));
	}

	public function pathInfo($fn,$schema,$type=self::FILE_PATH_TYPE) {
		// pre($fn,"path to match");
		if (self::FILE_PATH_TYPE === $type) {
			// Work only on the basename
			$fn = pathinfo($fn,PATHINFO_BASENAME);
			$extensions = $this->getExtensions();
			$extensionsRe = '\\.(' . implode('|',$extensions) . ')';
		}
		elseif (self::URL_PATH_TYPE === $type) {
			// this is an URL, evaluate the full path
			$extensionsRe = '';
		}
		$pattern = static::filenameSchemaToRegex($schema,$extensionsRe);

		$matches = [];
		if (preg_match($pattern,$fn,$matches)) {
			// we filter out the numeric keys of matches
			$keys = array_keys($matches);
			$numeric_keys = array_filter($keys,'is_numeric');
			return array_except($matches, $numeric_keys);
		// } else {
			// dump_r("$pattern NO match $fn");
		}
		return false;
	}

	// URLs -----------------------------------------------------------------

	public function filenameToUrl($meta) {
		$schemas = $this->getConf('url_map');
		foreach ($schemas as $filenameSchema => $urlSchema) {
			if ($props = $this->pathInfo($meta->filename,$filenameSchema,self::FILE_PATH_TYPE)) {
				return URL::to(static::replaceStrParts($urlSchema,array_merge($props,$meta->all())));
			}
		}
		throw new \Exception('Cannot transform filename "'.$meta->filename.'"');
	}

	// the schemas must return an ID, i.e. a file's name without the directory
	// and without an extension
	public function UrlToID($urlPath) {
		$schemas = $this->getConf('url_map');
		foreach ($schemas as $filenameSchema => $urlSchema) {
			if ($props = $this->pathInfo($urlPath,$urlSchema,self::URL_PATH_TYPE)) {
				return static::replaceStrParts(static::expandFileNameSchema($filenameSchema),$props);
			}
		}
		throw new UnknownURLSchemaException("Cannot transform URL '$urlPath'");
	}

	// URLs -----------------------------------------------------------------

	/**
	 * Replaces :symbols in URLs with actual values
	 * @param  string $schema  the url schema with :symbols
	 * @param  Closure|array $values values provider. The closure must return null if the key is not defined
	 * @return string an URL with values set
	 */
	static function replaceStrParts($schema,$values) {
		if (is_callable($values)) $getVal = $values;
		else $getVal = function($key) use ($values) {
			return isset($values[$key]) ? $values[$key] : null;
		};
		$keysFound = [];
		$matches = [];
		if (preg_match_all('/:[a-zA-Z0-9_]+/', $schema, $matches)) {
			foreach($matches[0] as $matchKey) {
				$key = substr($matchKey,1); // drop the colon
				$val = $getVal($key);
				if ($val !== null) {
					$schema = str_replace($matchKey, $val, $schema);
				}
			}
		}
		return $schema;
	}

	static function filenameSchemaToRegex($schema,$append) {
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

	static function expandFileNameSchema($schema) {
		switch ($schema) {
			case 'classic': return ':year-:month-:day-:slug';
			case 'simple': return ':slug';
			default: return $schema;
		}
	}

	public function index() {
		return $this->app['press.index'];
	}

	public function query($query, array $params=array()) {
		return $this->index()->query($query, $params);
	}

	public function all() {
		return $this->index()->all();
	}

	// Cache & Editing ------------------------------------------------------

	public function cache () {
		return $this->app->make('press.cache');
	}

	public function skipCache() {
		$this->mustCacheCurrentRequest = false;
	}

	public function isCacheableRequest($request,$response) {
		$routeOpts = $request->route()->getAction();
		return
			$this->mustCacheCurrentRequest
			&& isset($routeOpts['pressCache'])
			&& $routeOpts['pressCache'] == true
			&& 200 === $response->getStatusCode();
	}

	public function isEditing() {
		return $this->editing;
	}

	public function setEditing($editing=true) {
		$this->editing = $editing;
		if ($this->editing) $this->skipCache();
	}

	public function editingCacheInfo() {
		if (!$this->isEditing()) return null;
		$info = $this->cache()->cacheInfo($this->app->request);
		$info->indexMaxMTime = $this->index()->getModTime();
		$info->isCacheStale = $info->indexMaxMTime > $info->cacheTime;
		return $info;
	}

	// Themes management ----------------------------------------------------

	public function registerThemes() {
		$this->registerTheme('press', $this->themefilePath());
		foreach($this->getConf('load_themes') as $name => $dir) {
			$this->registerTheme($name, $dir);
		}
	}

	public function registerTheme($name, $dir) {
		\View::addNamespace($name, $dir);
		$this->registeredThemes[$name] = ['dir' => $dir];
	}

	public function getThemeDir($name) {
		$this->ensureThemeExists($name);
		return $this->registeredThemes[$name]['dir'];
	}

	public function ensureThemeExists($name) {
		if (!isset($this->registeredThemes[$name])) throw new \Exception(
			"Press theme '$name' does not exist."
		);
	}

	public function getDefaultThemeAssets() {
		return $this->getThemeAssets($this->getConf('theme','press'));
	}

	public function getThemeAssets($theme) {
		$cacheKey = "press::themefile->$theme";
		if (! isset($this->registeredThemes[$theme]))
			throw new \Exception("Unknown theme $theme");
		return $this->readTheme($theme);
	}

	public static function themefilePath() {
		return realpath(__DIR__ . '/../../views');
	}

	private function readTheme($theme) {
		if (isset($this->registeredThemes[$theme]['_themefile'])) {
			return $this->registeredThemes[$theme]['_themefile'];
		}
		$dir = $this->registeredThemes[$theme]['dir'];
		$infos = require "$dir/_themefile.php";
		$empty = [
			'styles'=>[],
			'scripts'=>[],
			'publishes'=>[],
		];
		$infos = array_merge($empty,$infos);
		$this->registeredThemes[$theme]['_themefile'] = $infos;
		return $infos;
	}

	public function themesPublishes() {
		$publishes = [];
		foreach ($this->registeredThemes as $theme => $_) {
			$publishes[] = $this->readTheme($theme)['publishes'];
		}
		return call_user_func_array('array_merge', $publishes);
	}

	public function hasHttpExceptionView($statusCode) {
		return View::exists($this->httpExceptionViewName($statusCode));
	}

	public function renderHttpException($statusCode) {
		$viewData = [
			'themeAssets' => $this->getDefaultThemeAssets()
		];
		$this->setEditing(false);
		return response()
			->view($this->httpExceptionViewName($statusCode), $viewData, $statusCode);
	}

	protected function httpExceptionViewName($statusCode) {
		return $this->namespaceView("errors.$statusCode");
	}

	protected function namespaceView($name) {
		$delim = \Illuminate\View\ViewFinderInterface::HINT_PATH_DELIMITER;
		return $this->getConf('theme') . $delim . $name;
	}

	// Routing --------------------------------------------------------------

	/**
	 * Maps a route to a query of articles
	 * @param  string $path    A route path like in Route::get(...)
	 * @param  string $query   A press query
	 * @param  array $_options An array of options
	 * @return \Illuminate\Routing\Route A laravel route set
	 * @todo split method in smaller parts
	 * @todo allow post|delete|etc ?
	 */
	public function listRoute($path, $query, array $_options=array()) {
		$as = 'press.'.crc32($query); // fast hash but more collision risks
		$options = array_merge(
			$this->listRouteOptsWithDefaults(),
			compact('query','as'),
			$_options
		);
		// now that the options are set, the user could have overriden the route
		// 'as' (the route name)
		$as = $options['as'];
		$options['base_route'] = $as; // we share the base route name with the paginated routes
		$routes = [Route::get($path, array_merge($options,['as' => $as]))]; // base route
		// we cannot have optional non-parameters parts in the url, so we must
		// define other routes
		if ($options['paginate']) {
			unset($options['as']);
			$p = PressPaginator::PAGE_NAME;
			// redirect page 1 to base path
			$routes[] = Route::get("$path/$p/1", function(\Illuminate\Routing\Route $route) use ($as) {
				$url = URL::route($as, $route->parameters(), $abs = false);
				return redirect($url,301);
			});
			// other pages
			$routes[] = Route::get("$path/$p/{page}", $options)->where('page','[0-9]+');
		}
		return new ChainableGroup($routes);
	}

	private function listRouteOptsWithDefaults() {
		static $base = [
			'paginate' => true,
			'pressCache' => true,
			'uses' => 'Lud\Press\PressPubController@showCollection',
		];
		return $base;
	}

	public function setRoutes($_SET_HOME_ROUTE=true) {
		require realpath(__DIR__ . '/../../routes.php');
	}

}

