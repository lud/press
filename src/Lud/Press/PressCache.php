<?php namespace Lud\Press;

use App;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;


class PressCache {

	private $req;

	const ext = '.cache.html';

	public function __construct(Request $req) {
		$this->req = $req;
	}

	public function writeFile($content) {
		$key = $this->currentKey();
		$path = $this->storagePath($key);
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
		$this->remove(PressFacade::getConf('storage_path'));
	}

	public function forget($key) {
		$this->remove($this->storagePath($key));
	}

	public function currentKey() {
		$key = $this->req->getPathInfo();
		if ("/" === $key) {
			// if it's the root page
			$key = '/_root';
		}
		return $key;
	}

	private function storagePath($x) {
		return $path = PressFacade::getConf('storage_path') . $x . self::ext;
	}

	private function fullStoragePath() {
		return $this->storagePath($this->currentKey());
	}

	private function remove($dirOrFile) {
		$fs = new Filesystem();
		if ($fs->exists($dirOrFile))
			$fs->remove($dirOrFile);
	}
}

