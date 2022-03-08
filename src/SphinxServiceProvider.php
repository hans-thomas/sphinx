<?php


	namespace Hans\Sphinx;

	use Hans\Sphinx\Contracts\SphinxContract;
	use Hans\Sphinx\Drivers\JwtUserProvider;
	use Hans\Sphinx\Guards\JwtGuard;
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
			Auth::provider( 'SphinxProvider', function() {
				return new JwtUserProvider;
			} );

			Auth::extend( 'SphinxJwtDriver', function( Application $app, $name, array $config ) {
				return $app->makeWith( JwtGuard::class,
					[ 'provider' => Auth::createUserProvider( $config[ 'provider' ] ) ] );
			} );

			$this->app->singleton( SphinxContract::class, function() {
				return new SphinxService();
			} );
		}

		/**
		 * Bootstrap any application services.
		 *
		 * @return void
		 */
		public function boot() {
			$this->publishes( [
				__DIR__ . '/../config/config.php' => config_path( 'sphinx.php' )
			], 'sphinx-config' );
			$this->mergeConfigFrom( __DIR__ . '/../config/config.php', 'sphinx' );
			$this->loadMigrationsFrom( __DIR__ . '/../migrations' );
		}
	}
