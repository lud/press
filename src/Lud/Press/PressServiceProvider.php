<?php namespace Lud\Press;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class PressServiceProvider extends ServiceProvider
{

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
        if (\App::runningInConsole()) {
            $this->consoleSetup();
        }
    }

    public static function confPath()
    {
        return realpath(__DIR__ . '/../../config/config.php');
    }

    private function consoleSetup()
    {
        $base = [
            __DIR__.'/../../../public' => base_path('public/packages/lud/press'),
            static::confPath() => config_path('press.php'),
        ];
        $themes = PressFacade::themesPublishes();
        $this->publishes(array_merge($base, $themes));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(static::confPath(), 'press');

        $this->app->bindShared('press', function($app) {
            //@todo use back laravel config loader
            $conf = config('press');
            $service = new PressService($app, $conf);
            $service->registerThemes();
            return $service;
        });

        $this->app->bindShared('press.index', function($app) {
            return new PressIndex();
        });

        $this->app->bindShared('press.cache', function($app) {
            return new PressCache($app->request);
        });

        $this->app->bind(
            'press.seo',
            config('press.seo_generator')
        );

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
