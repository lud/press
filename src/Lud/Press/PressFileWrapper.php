<?php namespace Lud\Press;

// wraps meta and file for twig

class PressFileWrapper
{

    private $file;
    private $meta;
    private $renderer;
    private $parent;

    public function __construct(PressFile $file, PressRenderer $renderer, MetaWrapper $parent = null)
    {
        $this->file = $file;
        $this->meta = $file->meta();
        $this->renderer = $renderer;
        $this->parent = $parent;
    }

    public function __isset($key)
    {
        return $this->meta->__isset($key); // allows calling article.someKey in the templates
    }

    public function __get($key)
    {
        return $this->meta->get($key); // allows calling article.someKey in the templates
    }

    public function parent()
    {
        return $this->meta->url();
    }

    public function url()
    {
        return $this->meta->url();
    }

    // imports the content of the file, not rendered. Must use the same
    // rendering engine as the importer (you can only import markdown in
    // markdown)
    public function import()
    {
        return $this->file->preRender($this->parent);
    }
}
