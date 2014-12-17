<?php namespace Lud\Press;

use Sunra\PhpSimple\HtmlDomParser;

class PressHTMLTransformer {

	private $dom;

	public function load($str) {
		$this->dom = str_get_html($str);
	}

	public function toHTML() {
		return $this->dom . '';
	}

	public function applyTransforms() {
		$this->setPressLinksURLs();
	}

	public function setPressLinksURLs() {
		$links = $this->dom->find('a');
		foreach ($links as $l) {
			$href = static::maybeTransformHref($l->href);
			$l->href = $href;
		}
	}

	static function maybeTransformHref($href) {
		if (starts_with($href,'press://')) {
			$filename = substr($href, strlen('press://'));
			try {
				$href = PressFacade::findFile($filename)->url();
			} catch (FileNotFoundException $e) {
				throw new LinkedFileNotFoundException("A file for the link $href could not be found.");
			}
		}
		return $href;
	}

}
