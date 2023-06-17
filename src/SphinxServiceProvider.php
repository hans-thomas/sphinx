<?php


	namespace Hans\Sphinx;

	use Hans\Sphinx\Contracts\SphinxContract;
	use Hans\Sphinx\Services\SphinxGuard;
	use Hans\Sphinx\Services\SphinxService;
	use Hans\Sphinx\Services\SphinxUserProvider;
	use Illuminate\Foundation\Application;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\ServiceProvider;

	class SphinxServiceProvider extends ServiceProvider {

		/**
		 * Register any application services.
		 *
		 * @return void
		 */
		public function register() {
			Auth::provider(
				'SphinxProvider',
				fn() => app(
					SphinxUserProvider::class,
					[ 'config' => $this->app[ 'config' ][ 'auth.providers.SphinxProvider' ] ]
				)
			);

			Auth::extend( 'SphinxDriver', function( Application $app, $name, array $config ) {
				return $app->makeWith(
					SphinxGuard::class,
					[
						'provider' => app(
							SphinxUserProvider::class,
							[ 'config' => $this->app[ 'config' ][ 'auth.providers.SphinxProvider' ] ]
						)
					]
				);
			} );

			$this->app->singleton( SphinxContract::class, fn() => new SphinxService );
		}

		/**
		 * Bootstrap any application services.
		 *
		 * @return void
		 */
		public function boot() {
			$this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'sphinx' );
			if ( $this->app->runningInConsole() ) {
				$this->loadMigrationsFrom( __DIR__ . '/../migrations' );
				$this->publishes(
					[
						__DIR__ . '/../config/config.php' => config_path( 'sphinx.php' )
					],
					'sphinx-config'
				);
			}
		}
	}
