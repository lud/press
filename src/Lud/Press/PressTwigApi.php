<?php namespace Lud\Press;

// this is a wrapper for twig

//@todo the renderer is not userd no more there

class PressTwigApi {

	private $index, $renderer, $contextMeta;

	// we get the rendering article in contextMeta to pass it to the included
	// files
	public function __construct(PressRenderer $renderer, MetaWrapper $contextMeta) {
		$this->index = PressFacade::index();
		$this->renderer = $renderer;
		$this->contextMeta = $contextMeta;
	}

	public function file($fileID) {
		$file = $this->index->getFile($fileID); // throws FileNotFoundException
		return new PressFileWrapper($file, $this->renderer, $this->contextMeta);
	}

	public function query($query) {
		return $this->index->query($query);
	}

}

class PressFileWrapper {

	private $file, $meta, $renderer, $parent;

	public function __construct(PressFile $file, PressRenderer $renderer, MetaWrapper $parent = null) {
		$this->file = $file;
		$this->meta = $file->meta();
		$this->renderer = $renderer;
		$this->parent = $parent;
	}

	public function __isset($key) {
		return $this->meta->__isset($key); // allows calling article.someKey in the templates
	}

	public function __get($key) {
		return $this->meta->get($key); // allows calling article.someKey in the templates
	}

	public function parent() {
		return $this->meta->url();
	}

	public function url() {
		return $this->meta->url();
	}

	// imports the content of the file, not rendered. Must use the same
	// rendering engine as the importer (you can only import markdown in
	// markdown)
	public function import() {
		return $this->file->preRender($this->parent);
	}

}
