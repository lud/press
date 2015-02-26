<?php namespace Lud\Press;

use Sunra\PhpSimple\HtmlDomParser;

class PressHTMLTransformer
{

    private $dom;

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
}
