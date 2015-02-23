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
		return new \DateTime($this->attributes['date']);
	}

	// getter override : we check the meta for thruthyness instead of checking
	// only if the key exists

	public function get($key,$default=null) {
		if ($key === 'date') return $this->dateTime();
		return @$this->attributes[$key] ?: value($default);
	}

	// magic call get/set override

	/**
	 * Handle dynamic calls to the container to set attributes.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return $this
	 */
	public function __call($method, $parameters)
	{
		throw new \Exception("Undefined MetaWrapper method $method");

	}

	// ArrayAccess overrides

	public function offsetSet($offset, $value) {
		throw new \Exception(get_class()." is immutable, tried to set '$offset'");
	}
	public function offsetUnset($offset) {
		throw new \Exception(get_class()." is immutable, tried to unset '$offset'");
	}



}
