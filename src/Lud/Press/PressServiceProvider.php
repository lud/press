<?php namespace Lud\Press;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class PressServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{

		$this->app->bindShared('press', function($app)
		{
			$confPath = realpath(__DIR__ . '/../../config/config.php');
			$conf = require $confPath;
			$service = new PressService($app,$conf);
			foreach ($service->getConf('themes_dirs', []) as $name => $dir) {
				$service->registerTheme($name,$dir);
			}
			return $service;
		});

		$this->app->bindShared('press.index', function($app)
		{
			return new PressIndex();
		});

		$this->app->bindShared('press.cache', function($app)
		{
			return new PressCache($app->request);
		});

		Paginator::currentPageResolver(function() {
			return $page = $this->app['request']->route()->parameter('page');
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
