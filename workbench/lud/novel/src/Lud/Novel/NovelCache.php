<?php namespace Lud\Novel;

use Cache;
use App;

class NovelCache {

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
			'cache_at' => time(),
			'key' => $key,
		];
	}

	public function currentKey() {
		$path = $this->req->getPathInfo();
		// the query string is normalized, so changing the order of the params
		// still hits the same cache
		$qs = $this->req->getQueryString();
		$key = md5("$path?$qs");
		return $key;
	}

	public function setCurrentRequestCacheContent($contentHTML) {
		return $this->forever($this->currentKey(),$contentHTML);
	}

	public function currentRefreshURL() {
		 return \Url::route('press.refresh_page_cache',[$this->currentKey()]);
	}

	private function forever($key,$content) {
		$cache = (object) [
			'content' => $content,
			'cache_at' => time(),
			'key' => $key,
		];
		$this->ramCache[$key] = $cache;
		Cache::forever($key,$cache);
		return $cache;
	}

}

