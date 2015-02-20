<?php namespace Lud\Press;

// This class must be extended by the user. We will get the route upon
// instantiation and then check if the route has a 'seo' key in its action
// parameters. If so, we will call this method on ourselves, if it is defined in
// the class or in the user's subclass

use Illuminate\Routing\Route;
use Illuminate\Container\Container;
use Illuminate\Routing\RouteDependencyResolverTrait;

class SeoGenerator {

	use RouteDependencyResolverTrait;

	private $container, $method;

	const ROUTE_ACTION_KEY = 'seo';

	public function __construct(Container $container, Route $route) {
		$this->container = $container;
		$opts = $route->getAction();
		$method = array_get($opts, self::ROUTE_ACTION_KEY, 'getDefaultMeta');
		if (!is_callable([$this, $method])) {
			$method = 'getDefaultMeta';
		}
		$this->method = $method;
	}

	public function getMeta() {
		$meta = $this->callWithDependencies($this, $this->method);
		return $this->wrap((array) $meta);
	}

	public function getDefaultMeta() {
		return [];
	}

	private function wrap($data) {
		return new MetaWrapper($data);
	}

}
