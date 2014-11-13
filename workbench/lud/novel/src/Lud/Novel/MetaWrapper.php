<?php namespace Lud\Novel;

class MetaWrapper implements \ArrayAccess {
	protected $meta;

	public function __construct($meta) {
		$this->meta = $meta;
	}

	public function __get($key) {
		return isset($this->meta[$key]) ? $this->meta[$key] : null;
	}

	public function get($key,$default=null) {
		return @$this->meta[$key] ?: $default;
	}

	public function all() { return $this->meta; }

	public function url() {
		return NovelFacade::filenameToUrl($this);
	}

	public function file() {

	}



	// ArrayAccess implementation

	public function offsetSet($offset, $value) {
		throw new \Exception(get_class()." is immutable, tried to set '$offset'");
	}
	public function offsetExists($offset) {
		return isset($this->meta[$offset]);
	}
	public function offsetUnset($offset) {
		throw new \Exception(get_class()." is immutable, tried to unset '$offset'");
	}
	public function offsetGet($offset) {
		return $this->get($offset);
	}

}
