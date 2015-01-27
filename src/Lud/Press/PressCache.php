<?php namespace Lud\Press;

use App;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;


class PressCache {

	private $req;

	const ext = '.html';

	public function __construct(Request $req) {
		$this->req = $req;
	}

	public function writeFile($content) {
		$uri = $this->requestPath();
		if ("/" === $uri) {
			// if it's the root page
			$uri = '/_root';
		}
		$path = $this->storagePath($uri);
		//@todo use flysystem
		$dir = dirname($path);
		if (!is_dir($dir)) mkdir($dir,0744,true);
		file_put_contents($path,$content);
	}

	public function cacheInfo() {
		$path = $this->fullStoragePath();
		if (is_file($path))
			return (object) [
				'cacheTime' => max(filemtime($path),filectime($path)),
			];
		else
			return (object) [
				'cacheTime' => time(),
			];
	}

	public function flush() {
		// delete the directory. trashy
		$this->remove($this->storagePath());
	}

	public function forget($path) {
		$this->remove($this->storagePath($path));
	}

	public function URLToRefreshCurrent() {
		return \URL::route('press.refresh_page_cache',[$this->requestPath()]);
	}

	private function requestPath() {
		return $this->req->getPathInfo();
	}

	private function storagePath($x='') {
		return $path = PressFacade::getConf('storage_path') . $x . self::ext;
	}

	private function fullStoragePath() {
		return $this->storagePath($this->requestPath());
	}

	private function remove($dirOrFile) {
		$fs = new Filesystem();
		return $fs->remove($dirOrFile);
	}
}

