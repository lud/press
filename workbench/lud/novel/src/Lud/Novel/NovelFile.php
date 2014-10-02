<?php namespace Lud\Novel;

use Symfony\Component\Yaml\Parser as YamlParser;
use \Skriv\Markup\Renderer as SkrivRenderer;

class NovelFile {

	const DEFAULT_META_PARSER = 'yaml';
	const DEFAULT_CONTENT_PARSER = 'skriv';

	protected $filename;
	protected $readOK = false;
	protected $rawMeta;
	protected $rawContent;
	protected $meta;
	protected $content;
	protected $app;

	public function __construct($app,$filename) {
		$this->app = $app;
		$this->filename = $filename;
	}

	public function content($parserName=null) {
		if ($this->content === null) {
			$this->parse(['content'=>$parserName]);
		}
		return $this->content;
	}

	public function meta($parserName=null) {
		if ($this->meta === null) {
			$this->parse(['meta'=>$parserName]);
		}
		return $this->meta;
	}

	public function parse($conf=[]) {
		if (empty($conf['meta'])) $conf['meta'] = self::DEFAULT_META_PARSER;
		if (empty($conf['content'])) $conf['content'] = self::DEFAULT_CONTENT_PARSER;
		$this->readFileIfNotRead();
		list($this->meta,$this->content) = [
			$this->parseMeta($conf['meta']),
			$this->parseContent($conf['content'])
		];
		return  [$this->meta,$this->content];
	}

	public function parseMeta($parserName=null) {
		if (null === $parserName) $parserName = self::DEFAULT_META_PARSER;
		$this->readFileIfNotRead();
		$parser = $this->getParser($parserName);

		// Meta present in the file

		$headerMeta = $parser($this->rawMeta);
		if (is_null($headerMeta)) $headerMeta = [];
		if (isset($headerMeta['date'])) {
			$headerMeta['year'] = date('Y',$headerMeta['date']);
			$headerMeta['month'] = date('m',$headerMeta['date']);
			$headerMeta['day'] = date('d',$headerMeta['date']);
		}

		// Default meta :
		if (!isset($headerMeta['title'])) $headerMeta['title'] = ''; // just to be present
		if (!isset($headerMeta['tags'])) $headerMeta['tags'] = []; // hope most people want tags
		// if only one tag is set, or a comma list, we make it an array
		if (!is_array($headerMeta['tags'])) $headerMeta['tags'] = array_map('trim',explode(',',$headerMeta['tags']));


		// Additional metadata based on filename. Meta in header can override

		$fileMeta = ['filename' => $this->filename];
		// then we try to figure out a schema. We try all the defined schemas in
		// the config
		foreach(\Config::get('novel::config.filename_schemas') as $schema) {
			if (($fnInfo = NovelService::filenameInfo($this->filename,$schema)) !== false) {
				// Here we got some infos such as date from filename
				$fileMeta = array_merge($fileMeta,$fnInfo);
				break; // stop on first match. The list in config must be ordered by path complexion
			}
		}
		// if the directory of the file is the base directory, the relpath meta
		// is empty. if the file is in a subdirectory (or more), we store this
		// path as a string
		$realDir = realpath(dirname($this->filename));
		$baseReal = realpath($this->app['novel']->getConf()['base_dir']);
		$dirDiff = trim(substr($realDir,strlen($baseReal)), DIRECTORY_SEPARATOR);
		$fileMeta['dirs'] =
			empty($dirDiff)
				? []
				: explode(DIRECTORY_SEPARATOR, $dirDiff);
		// store the relative normalized path too
		$realPath = realpath($this->filename);
		$pathDiff = trim(substr($realPath,strlen($baseReal) + 1)); // +1 to trim the starting slash (this is a relative path)
		// force slashes
		$fileMeta['rel_path'] = str_replace('\\', '/', $pathDiff);

		$this->meta = new MetaWrapper(array_merge($fileMeta, $headerMeta));
		return $this->meta;
	}

	public function parseContent($parserName) {
		$this->readFileIfNotRead();
		$parser = $this->getParser($parserName);
		$this->content = $parser($this->rawContent);
		return $this->content;
	}

	protected function readFileIfNotRead() {
		if ($this->readOK) return;
		$sep = $this->app['novel']->getConf()['meta_sep'];
		$raw = file_get_contents($this->filename);
		$rawParts = explode($sep,$raw);
		if (count($rawParts) > 1) {
			$this->rawMeta = array_shift($rawParts);
		} else {
			$this->rawMeta = '';
		}
		$this->rawContent = implode($sep,$rawParts);
		$this->readOK = true;
	}

	public function url() {
		return $this->meta()->url();
	}

	protected function getParser ($name) {
		$parserConfig = $this->app['novel']->getConf($name,[]);
		switch ($name) {
			case 'yaml':
				return function($str) {
					$parser = new YamlParser();
					return $parser->parse($str);
				};
			case 'skriv':
				return function($str) use ($parserConfig) {
					$renderer = SkrivRenderer::factory('html',$parserConfig);
					$html = $renderer->render($str);
					$footnotes_html = $renderer->getFootnotes();
					$footnotes = $renderer->getFootnotes(true);
					return ['html' => $html, 'footnotes_html' => $footnotes_html, 'footnotes' => $footnotes];
				};
			default: throw new \Exception("Unknown parser $name");
		}
	}


}
