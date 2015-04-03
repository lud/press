<?php namespace Lud\Press;

use Closure;
use Michelf\Markdown;
use Michelf\MarkdownExtra;
use Skriv\Markup\Renderer as SkrivRenderer;
use Twig_Environment;
use Twig_Loader_Array;

class PressRenderer
{

	/**
	 * Registers Closure instances to get HTML content to inject into the
	 * rendered content. Different instances are often user to render & pre-
	 * render (one for twig, the other for markdown), so we share the generators
	 * and the references (see below).
	 * @var array
	 */
	private static $prerenderedContentFuns = [];

	/**
	 * A reference to identify registered content generators
	 * @var integer
	 */
	private static $generatorRef = 0;

    // treats the content as a full content document and transforms it to HTML
    public function transform($parserType, $preRendered)
    {
        $config = PressFacade::getConf($parserType, []);
        $method = 'transform'.lcfirst($parserType);
        return $html = call_user_func([$this,$method], $preRendered, $config);
    }

    // treats the content as a twig template and renders it
    public function preRender($tplStr, MetaWrapper $meta, MetaWrapper $parentMeta = null)
    {
        $twigData = [
            'article' => $meta,
            'press' => new PressTwigApi($this, $meta),
            'parent' => $parentMeta
        ];
        $twigLoader = new Twig_Loader_Array([
            '__article_content' => $tplStr
        ]);
        $twig = new Twig_Environment($twigLoader, [
            'autoescape' => false,
            // 'strict_variables' => true,
            'optimizations' => 0
        ]);
        return $preRendered = $twig->render('__article_content', $twigData);
    }

    protected function transformMarkdown($str, $config)
    {
        $html = MarkdownExtra::defaultTransform($str);
        //@todo refactor parsers functions
        $trsf = new PressHTMLTransformer($this);
        $trsf->load($html);
        $trsf->applyTransforms();
        return ['html' => $trsf->toHTML(), 'footnotes_html' => ''];
    }

    protected function transformSkriv($str, $config)
    {
        $renderer = SkrivRenderer::factory('html', $config);
        $html = $renderer->render($str);
        $footnotes_html = $renderer->getFootnotes();
        $footnotes = $renderer->getFootnotes(true);
        return ['html' => $html, 'footnotes_html' => $footnotes_html];
    }

    protected function transformHtml($str, $config)
    {
        $trsf = new PressHTMLTransformer;
        $trsf->load($str);
        $trsf->applyTransforms();
        return ['html' => $trsf->toHTML(), 'footnotes_html' => ''];
    }

    public function insertPrerenderedBlock(Closure $contentGenerator, $format='md') {
    	$ref = $this->registerContentGenerator($contentGenerator);
    	$tag = PressHTMLTransformer::PRESS_INSERT_TAG;
    	switch ($format) {
    		// we just return a tag that will be replaced by the generated
    		// content in the render phase. We need a block that will be left
    		// as-is by the markdown parser

    		case 'md' : return "<$tag markdown=\"1\" press-ref='$ref'></$tag>";
    		default: throw new \Exception('Unknown target format');
    	}
    }

    public function getGeneratedContent($ref) {
    	$generate = self::$prerenderedContentFuns[$ref];
    	return $generate();
    }

    protected function registerContentGenerator(Closure $contentGenerator) {
    	// Every generator registered use anincrement of the reference so there
    	// are no index clashes in the static array
    	$ref = self::$generatorRef += 1;
    	self::$prerenderedContentFuns[$ref] = $contentGenerator;
    	return $ref;
    }

}
