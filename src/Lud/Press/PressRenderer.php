<?php namespace Lud\Press;

use Skriv\Markup\Renderer as SkrivRenderer;
use Michelf\Markdown;
use Michelf\MarkdownExtra;
use Twig_Environment;
use Twig_Loader_Array;

class PressRenderer
{


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
        $trsf = new PressHTMLTransformer;
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
}
