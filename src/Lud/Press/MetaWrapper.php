<?php namespace Lud\Press;

use Illuminate\Support\Fluent;

class MetaWrapper extends Fluent implements \ArrayAccess {


	public function url() {
		return PressFacade::filenameToUrl($this);
	}

	public function all() { return $this->getAttributes(); }

	public function formatDate($format='Y-m-d') {
		return $this->dateTime()->format($format);
	}

	public function dateTime() {
		return new \DateTime($this->date);
	}

	// getter override : we check the meta for thrueness instead of checking
	// only if the key exists

	public function get($key,$default=null) {
		return @$this->attributes[$key] ?: value($default);
	}

	// ArrayAccess overrides

	public function offsetSet($offset, $value) {
		throw new \Exception(get_class()." is immutable, tried to set '$offset'");
	}
	public function offsetUnset($offset) {
		throw new \Exception(get_class()." is immutable, tried to unset '$offset'");
	}



}
