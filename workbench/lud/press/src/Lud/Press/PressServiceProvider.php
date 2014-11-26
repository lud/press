<?php namespace Lud\Press;

use \Illuminate\Pagination\Paginator;
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
		$this->package('lud/press');

		$this->app->bindShared('press', function($app)
		{
			$service = new PressService($app,\Config::get('press::config'));
			foreach (\Config::get('press::themes_dirs',[]) as $name => $dir) {
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
