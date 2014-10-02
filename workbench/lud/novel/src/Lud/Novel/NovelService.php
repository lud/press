<?php namespace Lud\Novel;

use View;
use Cache;
use Symfony\Component\Finder\Finder;

class NovelService {

	protected $conf = [];
	protected $app;

	public function __construct($app,$conf=[]) {
		$this->configure($conf);
		$this->app = $app;
	}

	public function findFile($_conf=[]) {
		$conf = $this->configure($_conf);
		$filename = static::filenameJoin([
			$conf['base_dir'],
			ltrim($conf['filename'])
		]);
		if (! is_file($filename)) {
			return $conf['onFileMissing']($filename);
		} else {
			return new NovelFile($this->app, $filename);
		}
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

	public function publish($conf) {
		if (is_string($conf)) $conf = ['filename' => $conf];
		$post = $this->findFile($conf);
		$layout = $post->meta()->get('layout','default');
		return View::make($layout)
			->with('meta',$post->meta())
			->with('content',$post->content())
			->with('index', $this->index())
		;
	}

	static public function filenameJoin($parts) {
		return implode(DIRECTORY_SEPARATOR,$parts);
	}

	static function take($string,$amount,$char='-') {
		$t = explode($char,$string);
		$l = [];
		while ($amount--) {
			$l[] = array_shift($t);
		}
		return [$l,implode($char,$t)];
	}

	static function filenameInfo($fn,$schema) {
		// Work only on the basename
		$fn = pathinfo($fn,PATHINFO_BASENAME);
		// We have some classic schemas registered
		switch($schema) {
			// Skriv -------------------------------------------
			case 'classic':
				$pattern = '@(?P<year>[0-9]{4})-(?P<month>[0-9]{2})-(?P<day>[0-9]{2})-(?P<slug>.+)\.(md|sk)@';
				break;
			case 'simple':
				$pattern = '@(?P<slug>.+)\.(md|sk)@';
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
				$pattern = '@' . str_replace($scParts,$reParts,$schema) . '@';
		}
		$matches = [];
		if (preg_match($pattern,$fn,$matches)) {
			// we filter out the numeric keys of matches
			$keys = array_keys($matches);
			$numeric_keys = array_filter($keys,'is_numeric');
			return array_except($matches, $numeric_keys);
		}
		return false;
	}

	static function filenameTransform($fn,$meta,$schemas) {
		foreach ($schemas as $pathSchema => $urlSchema) {
			if ($props = static::filenameInfo($fn,$pathSchema)) {
				return static::replaceStrParts($urlSchema,array_merge($props,$meta->all()));
			}
		}
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

	public function index($minutes=0) {
		return $this->app['novel.index'];
	}

	public function query($query) {
		return $this->index()->query($query);
	}

	public function all() {
		return $this->index()->all();
	}

}

