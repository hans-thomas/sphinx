<?php

namespace Hans\Sphinx;

use Hans\Sphinx\Services\SphinxGuard;
use Hans\Sphinx\Services\SphinxService;
use Hans\Sphinx\Services\SphinxUserProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class SphinxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('sphinx-service', SphinxService::class);

        Auth::extend('sphinxJwt', function (Application $app, $name, array $config) {
            Auth::provider(
                'sphinx',
                fn () => $app->makeWith(
                    SphinxUserProvider::class,
                    ['model' => $this->app['config']["auth.providers.{$config['provider']}.model"]]
                )
            );

            return $app->makeWith(
                SphinxGuard::class,
                [
                    'provider' => Auth::createUserProvider(
                        $config['provider']
                    ),
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sphinx');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../migrations');
            $this->publishes(
                [
                    __DIR__.'/../config/config.php' => config_path('sphinx.php'),
                ],
                'sphinx-config'
            );
        }
    }
}
