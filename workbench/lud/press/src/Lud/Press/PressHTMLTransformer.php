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
			$href = PressFacade::findFile($filename)->url();
		}
		return $href;
	}

}
