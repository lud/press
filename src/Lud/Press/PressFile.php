<?php namespace Lud\Press;

use Skriv\Markup\Renderer as SkrivRenderer;
use Michelf\Markdown;
use Michelf\MarkdownExtra;
use Symfony\Component\Yaml\Parser as YamlParser;

class PressFile {

	protected $filename;
	protected $readOK = false;
	protected $rawMeta;
	protected $rawContent;
	protected $meta;
	protected $content;

	public function __construct($filename,MetaWrapper $meta=null) {
		$this->filename = $filename;
		$this->meta = $meta;
	}

	public function content($parserName=null) {
		if ($this->content === null) {
			$this->parse(['content'=>$parserName]);
		}
		return $this->content;
	}

	public function meta() {
		if ($this->meta === null) {
			$this->parseMeta();
		}
		return $this->meta;
	}

	public function parse() {
		$this->readFileIfNotRead();
		list($this->meta,$this->content) = [
			$this->parseMeta(),
			$this->parseContent()
		];
		return  [$this->meta,$this->content];
	}

	public function parseMeta() {

		//@todo refactor : set an array of defaults, then build the meta object
		// from the defaults and then override

		$this->readFileIfNotRead();
		$parser = new YamlParser();

		// We figure out the ID of the file, i.e. a unique string that match
		// only the significant parts of the file name. So we cannot have two
		// files called aaa-bbb-ccc.MD & aaa-bbb-ccc.sk, event in different
		// directories, because both will have aaa-bbb-ccc as an ID, whatever
		// the directory is.

		$fileID = pathinfo($this->filename,PATHINFO_FILENAME);
		$baseMeta = [
			'filename' => $this->filename,
			'id' => $fileID,
			'mtime' => filemtime($this->filename),
		];

		// Meta present in the file

		$headerMeta = $parser->parse($this->rawMeta);
		if (is_null($headerMeta)) $headerMeta = [];

		// Default meta :
		if (!isset($headerMeta['title'])) $headerMeta['title'] = ''; // just to be present
		// if only one tag is set, or a comma list, we make it an array
		if (!isset($headerMeta['tags'])) $headerMeta['tags'] = []; // hope most people want tags
		if (!is_array($headerMeta['tags'])) $headerMeta['tags'] = array_map('trim',explode(',',$headerMeta['tags']));


		if (!isset($headerMeta['theme'])) $headerMeta['theme'] = (string) PressFacade::getConf('theme');
		PressFacade::ensureThemeExists($headerMeta['theme']);
		if (!isset($headerMeta['layout'])) $headerMeta['layout'] = 'layout.not.set'; // here throw an err ?

		// merge header
		$baseMeta = array_merge($baseMeta,$headerMeta);

		// We read some meta from the filename (y-m-d, slug, ...)
		$filenameMeta = [];
		foreach(PressFacade::getConf('filename_schemas') as $schema) {
			if (($fnInfo = PressFacade::pathInfo($this->filename,$schema)) !== false) {
				$filenameMeta = $fnInfo;
				break; // stop on first match. The list in config must be ordered by path complexion
			}
		}

		// figure out the date. date set on the header has higher priority.
		// we need year, month & day to create a date from the filename
		// we default on filemtime
		if (!isset($baseMeta['date'])) {
			if (isset($filenameMeta['year'])
			&& isset($filenameMeta['month'])
			&& isset($filenameMeta['day'])) {
				$baseMeta['date'] = implode('-',[
					$filenameMeta['year'],
					$filenameMeta['month'],
					$filenameMeta['day']
				]);
			} else {
				$baseMeta['date'] = date('Y-m-d',$baseMeta['mtime']);
			}
		}

		// if the directory of the file is the base directory, the relpath meta
		// is empty. if the file is in a subdirectory (or more), we store this
		// path as a string
		$realDir = realpath(dirname($this->filename));
		$baseReal = realpath(PressFacade::getConf('base_dir'));
		$dirDiff = trim(substr($realDir,strlen($baseReal)), DIRECTORY_SEPARATOR);
		$baseMeta['dirs'] =
			empty($dirDiff)
				? []
				: explode(DIRECTORY_SEPARATOR, $dirDiff);
		// store the relative normalized path too
		$realPath = realpath($this->filename);
		$pathDiff = trim(substr($realPath,strlen($baseReal) + 1)); // +1 to trim the starting slash (this is a relative path)
		// force slashes
		$baseMeta['rel_path'] = str_replace('\\', '/', $pathDiff);
		$this->meta = new MetaWrapper($baseMeta);

		// set the theme/layout

		$this->meta->layout = $this->meta->theme . '::' . $this->meta->layout;

		return $this->meta;
	}

	public function parseContent() {
		$this->readFileIfNotRead();
		$parser = $this->getParser($this->filename);
		$this->content = $parser($this->rawContent);
		return $this->content;
	}

	protected function readFileIfNotRead() {
		if ($this->readOK) return;
		$sep = PressFacade::getConf('meta_sep');
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

	protected function getParser ($filename) {

		$extension = pathinfo($filename,PATHINFO_EXTENSION);

		switch ($extension) {
			case 'md':
				$name = 'markdown';
				break;
			case 'sk':
				$name = 'skriv';
				break;
			case 'html':
			case 'htm':
				$name = 'html';
				break;
			default: throw new \Exception("No parser defined for extension $extension");
		}

		$parserConfig = PressFacade::getConf($name,[]);

		switch ($name) {
			case 'markdown':
				return function($str) use ($parserConfig) {
					$html = MarkdownExtra::defaultTransform($str);
					//@todo refactor parsers functions
					$trsf = new PressHTMLTransformer;
					$trsf->load($html);
					$trsf->applyTransforms();
					return ['html' => $trsf->toHTML(), 'footnotes_html' => ''];
				};
			case 'skriv':
				return function($str) use ($parserConfig) {
					$renderer = SkrivRenderer::factory('html',$parserConfig);
					$html = $renderer->render($str);
					$footnotes_html = $renderer->getFootnotes();
					$footnotes = $renderer->getFootnotes(true);
					return ['html' => $html, 'footnotes_html' => $footnotes_html];
				};
			case 'html':
				return function($str) use ($parserConfig) {
					$trsf = new PressHTMLTransformer;
					$trsf->load($str);
					$trsf->applyTransforms();
					return ['html' => $trsf->toHTML(), 'footnotes_html' => ''];
				};
			default: throw new \Exception("Unknown parser $name");
		}
	}


}
