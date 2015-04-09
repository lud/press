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
        return $this->dom->__toString();
    }

    public function applyTransforms()
    {
        $this->setPressLinksURLs();
        $this->setBootstrapClasses();
        $this->insertGeneratedContent();
    }

    public function stripMarkdownExtraFootNotes()
    {
        $containers = $this->dom->find('div.footnotes');
        if (!isset($containers[0])) {
            return '';
        }
        $container = $containers[0];
        $container->find('hr')[0]->outertext = '';
        $html = $container->innertext;
        $container->outertext = '';
        return $html;
    }

    private function setPressLinksURLs()
    {
        $links = $this->dom->find('a');
        foreach ($links as $l) {
            $href = static::maybeTransformHref($l->href);
            $l->href = $href;
        }
    }


    private function setBootstrapClasses()
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

    private function insertGeneratedContent()
    {
        $placeholders = $this->dom->find(self::PRESS_INSERT_TAG.'[press-ref]');
        foreach ($placeholders as $node) {
            $ref = $node->__get('press-ref');
            $node->outertext = $this->contentProvider->getGeneratedContent($ref);
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

    public static function unwrapFootnotes($divFootnotesHTML)
    {
        $parser = str_get_html($divFootnotesHTML, null, null, null, false);
        $container = $parser->find('div.footnotes')[0];
        return $container->innertext;
    }
}
