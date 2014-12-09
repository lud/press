<?php namespace Lud\Press;

use Cache;
use App;

class PressCache {

	private $req;
	private $ramCache = [];

	public function __construct($request) {
		$this->req = $request;
	}

	public function hasCurrentRequest() {
		return $this->has($this->currentKey());
	}

	public function has($key) {
		return Cache::has($key);
	}

	public function forget($key) {
		return Cache::forget($key);
	}

	public function flush() {
		return Cache::flush();
	}

	public function getCurrentRequest() {
		return $this->get($this->currentKey());
	}

	// this is the default getter, returns the current request cache or a fake
	// cache if the request has no cache
	public function current() {
		$cache = $this->getCurrentRequest();
		if (null === $cache)
			$cache = $this->getFakeCache($this->currentKey());
		return $cache;
	}

	public function get($key) {
		return isset($this->ramCache[$key])
			? $this->ramCache[$key]
			: Cache::get($key);
	}

	public function getFakeCache($key) {
		return (object) [
			'content' => null,
			'cacheTime' => time(),
			'key' => $key,
		];
	}

	public function currentKey() {
		$keysource = $this->req->getPathInfo();
		// if we would cache with different query_string parameters, we should
		// add the following line. (the parameters are alphabetically ordered
		// but still anyone could fill the cache by send resquests with random
		// param names and values)
		// $keysource .= '?'.$this->req->getQueryString();
		$key = md5("$keysource");
		return $key;
	}

	public function setCurrentRequestCacheContent($contentHTML) {
		return $this->forever($this->currentKey(),$contentHTML);
	}

	public function currentRefreshURL() {
		 return \URL::route('press.refresh_page_cache',[$this->currentKey()]);
	}

	private function forever($key,$content) {
		$cache = (object) [
			'content' => $content,
			'cacheTime' => time(),
			'key' => $key,
		];
		$this->ramCache[$key] = $cache;
		Cache::forever($key,$cache);
		return $cache;
	}

}

