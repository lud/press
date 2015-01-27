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
		\Event::fire('press.mount', []);
		$this->publishes([
			__DIR__.'/../../../public' => base_path('public/packages/lud/press'),
		]);

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('press', function($app)
		{
			//@todo use back laravel config loader
			$confPath = realpath(__DIR__ . '/../../config/config.php');
			$conf = require $confPath;
			$service = new PressService($app,$conf);
			$service->registerTheme('press',$service->themefilePath());
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
			return $this->app['request']->route()->parameter('page')
				?: $this->app['request']->input('page');
		});
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
