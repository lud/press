<?php namespace Lud\Press;

class Collection extends \Illuminate\Support\Collection {

	public function where($key, $value) {
		$values = (array) $value;
		return $this->filter(function($meta) use ($key, $values) {
			if (!isset($meta[$key])) return false;
			if (is_array($meta[$key])) { // example : $meta[$key] is a list of tags
				// here we return true if we find at least one $meta.key member
				// in $values. implement whereAll to ensure all is present
				// pre ($values,"looking for");
				// pre ($meta[$key],"have");
				// pre(count(array_intersect($meta[$key], $values)) > 0, "result");
				return count(array_intersect($meta[$key], $values)) > 0;
			}
			// else meta.key is scalar
			return in_array($meta[$key], $values);
		});
	}

	public function drop($amount) {
		return $this->slice($amount, $length=null, $preserveKeys=true);
	}

}
