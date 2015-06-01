<?php namespace Lud\Press;

use App;
use Log;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class PressCache
{

    private $req;

    const EXT = '.cache.html';

    public function __construct(Request $req)
    {
        $this->req = $req;
    }

    public function writeFile($content)
    {
        $key = $this->currentKey();
        $path = $this->storagePath($key);
        //@todo use flysystem
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
            $this->logRequestInfo('PressCache created directory', ['dir' => $dir]);
        }
        file_put_contents($path, $content);
        $this->logRequestInfo('PressCache wrote cache file', ['path' => $path, 'size' => strlen($content)]);
    }

    public function cacheInfo()
    {
        $path = $this->fullStoragePath();
        if (is_file($path)) {
            return (object) [
                'cacheTime' => max(filemtime($path), filectime($path)),
            ];
        } else {
            return (object) [
                'cacheTime' => time(),
            ];
        }
    }

    public function flush()
    {
        // delete the directory. trashy
        $this->remove(PressFacade::getConf('storage_path'));
        $this->logRequestInfo('PressCache flushed cache', []);
        // force the rebuild of the index. This is a convenience call, we assume
        // the user wants to flush ALL the cache, including the cached index
        PressFacade::index()->rebuild();
    }

    public function forget($key)
    {
        $path = $this->storagePath($key);
        $this->remove($path);
        $this->logRequestInfo('PressCache forgot cache', ['key' => $key, 'path' => $path]);
    }

    public function currentKey()
    {
        $key = $this->req->getPathInfo();
        if ("/" === $key) {
            // if it's the root page
            $key = '/_root';
        }
        return $key;
    }

    private function storagePath($x)
    {
        return $path = PressFacade::getConf('storage_path') . $x . self::EXT;
    }

    private function fullStoragePath()
    {
        return $this->storagePath($this->currentKey());
    }

    private function remove($dirOrFile)
    {
        $fs = new Filesystem();
        if ($fs->exists($dirOrFile)) {
            $fs->remove($dirOrFile);
        }
    }

    private function logRequestInfo($message, $contextInfo)
    {
        $serverInfo =
        $infos = array_merge(
            $contextInfo,
            array_only($_SERVER, ['HTTP_USER_AGENT','HTTP_REFERER']),
            [ 'url' => $this->req->fullUrl()
            , 'editing' => PressFacade::isEditing()
            ]
        );
        return Log::debug($message, $infos);
    }
}
