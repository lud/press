<?php namespace Lud\Press;

class Collection extends \Illuminate\Support\Collection {

	public function where($key, $value) {
		$values = (array) $value;
		return $this->filter(function($meta) use ($key, $values) {
			if (!isset($meta[$key])) return false;
			if (is_array($meta[$key])) {
				// example : $meta[$key] is a list of tags, here we return true
				// if we find at least one $meta[$key] member in $values.
				return count(array_intersect($meta[$key], $values)) > 0;
			}
			// else meta[key] is scalar
			return in_array($meta[$key], $values);
		});
	}

	public function getPaginator($currentPage) {
		return new PressPaginator($this, $this->count(), $currentPage);
	}

	static function byDateDesc() {
		return function(MetaWrapper $fileA, MetaWrapper $fileB) {
			return $fileA->formatDate() < $fileB->formatDate();
		};
	}
}
