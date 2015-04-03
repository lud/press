<?php namespace Lud\Press;

use Sunra\PhpSimple\HtmlDomParser;

class PressHTMLTransformer
{

    // the HTML tag name that can be replaced by pre-rendered content having a
    // "press-ref" attribute provided
    const PRESS_INSERT_TAG = 'article';

    private $dom;

    private $contentProvider;

    public function __construct($contentGeneratorProvider)
    {
        $this->contentProvider = $contentGeneratorProvider;
    }

    public function load($str)
    {
        $this->dom = str_get_html($str, null, null, null, false);
    }

    public function toHTML()
    {
        return $this->dom . '';
    }

    public function applyTransforms()
    {
        $this->setPressLinksURLs();
        $this->setBootstrapClasses();
        $this->insertGeneratedContent();
    }

    public function setPressLinksURLs()
    {
        $links = $this->dom->find('a');
        foreach ($links as $l) {
            $href = static::maybeTransformHref($l->href);
            $l->href = $href;
        }
    }

    public static function maybeTransformHref($href)
    {
        if (starts_with($href, 'press://')) {
            $filename = substr($href, strlen('press://'));
            try {
                $href = PressFacade::findFile($filename)->url();
            } catch (FileNotFoundException $e) {
                throw new LinkedFileNotFoundException("A file for the link $href could not be found.");
            }
        }
        return $href;
    }

    public function setBootstrapClasses()
    {
        $tables = $this->dom->find('table');
        foreach ($tables as $node) {
            $node->class .= ' table';
        }
        $imgs = $this->dom->find('img');
        foreach ($imgs as $node) {
            $node->class .= ' img-responsive';
        }
    }

    public function insertGeneratedContent()
    {
        $placeholders = $this->dom->find(self::PRESS_INSERT_TAG.'[press-ref]');
        foreach ($placeholders as $node) {
            $ref = $node->__get('press-ref');
            $node->outertext = $this->contentProvider->getGeneratedContent($ref);
        }
    }
}
