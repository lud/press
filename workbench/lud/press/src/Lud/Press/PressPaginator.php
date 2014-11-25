<?php namespace Lud\Press;

use Illuminate\Pagination\LengthAwarePaginator;

class PressPaginator extends LengthAwarePaginator {

	const PAGE_NAME = 'p';

	protected $pageName = self::PAGE_NAME;


	public function url($page) {
		if ($page <= 0) $page = 1;
		$url = $this->path.$this->pageName.'/'.$page;
		// if we have a query string, add it
		if (count($this->query)) {
			$url .= '?'.http_build_query($this->query, null, '&');
		}
		// add the hash part
		$url .= $this->buildFragment();
		return $url;
	}

}

