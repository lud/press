<?php namespace Lud\Press;

// this is a wrapper for twig

//@todo the renderer is not userd no more there

class PressTwigApi
{

    private $index;
    private $renderer;
    private $contextMeta;

    // we get the rendering article in contextMeta to pass it to the included
    // files
    public function __construct(PressRenderer $renderer, MetaWrapper $contextMeta)
    {
        $this->index = PressFacade::index();
        $this->renderer = $renderer;
        $this->contextMeta = $contextMeta;
    }

    public function file($fileID)
    {
        $file = $this->index->getFile($fileID); // throws FileNotFoundException
        return new PressFileWrapper($file, $this->renderer, $this->contextMeta);
    }

    public function query($query)
    {
        return $this->index->query($query);
    }
}
