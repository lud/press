<?php namespace Lud\Novel;

use Illuminate\Support\ServiceProvider;

class NovelServiceProvider extends ServiceProvider {

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
		$this->package('lud/novel');
		$this->app->bindShared('novel', function($app)
		{
			return new NovelService($app,\Config::get('novel::config'));
		});
		$this->app->bindShared('novel.index', function($app)
		{
			return new NovelIndex();
		});
		$this->app->bindShared('novel.cache', function($app)
		{
			return new NovelCache($app->request);
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
