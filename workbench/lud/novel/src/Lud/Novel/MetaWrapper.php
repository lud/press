<?php namespace Lud\Novel;

class MetaWrapper {
	protected $meta;

	public function __construct($meta) {
		$this->meta = $meta;
	}

	public function __get($key) {
		return isset($this->meta[$key]) ? $this->meta[$key] : null;
	}

	public function get($key,$default=null) {
		return isset($this->meta[$key]) ? $this->meta[$key] : $default;
	}

	public function all() { return $this->meta; }

}
