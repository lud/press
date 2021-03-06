<?php namespace Lud\Press;

use Illuminate\Pagination\LengthAwarePaginator;

class PressPaginator extends LengthAwarePaginator
{

    const PAGE_NAME = 'p';

    protected $pageName = self::PAGE_NAME;

    // We do not want page number in query string since page cache ignores query
    // string, so we override the url function
    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }
        $url = $this->path.$this->pageName.'/'.$page;
        // if we have a query string, add it
        if (count($this->query)) {
            $url .= '?'.http_build_query($this->query, null, '&');
        }
        // add the hash part
        $url .= $this->buildFragment();
        return $url;
    }

    public function setBasePath($x)
    {
        //@todo check when "setBaseUrl" is back in laravel 5.0
        $this->path = $x != '/' ? rtrim($x, '/').'/' : $x;
    }
}
