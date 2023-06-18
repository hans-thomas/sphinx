<?php

	namespace Hans\Sphinx\Tests;

	use App\Models\User;
	use AreasEnum;
	use Hans\Horus\Facades\HorusSeeder as Horus;
	use Hans\Horus\HorusServiceProvider;
	use Hans\Horus\Models\Role;
	use Hans\Sphinx\SphinxServiceProvider;
	use Illuminate\Foundation\Application;
	use Illuminate\Foundation\Testing\RefreshDatabase;
	use Illuminate\Routing\Router;
	use Orchestra\Testbench\TestCase as BaseTestCase;
	use RolesEnum;

	class TestCase extends BaseTestCase {
		use RefreshDatabase;

		/**
		 * Setup the test environment.
		 */
		protected function setUp(): void {
			parent::setUp();

			config()->set(
				'sphinx.secrest',
				'XELnlAjESvqWDS3utBoN9cEA8eF3PlTtyXJ1OmCUIhxfIJKdePkoof8aKCbfucOCqpuygSDv4ZobA4936UXqzshfJrw'
			);
			config()->set(
				'sphinx.role_model',
				Role::class
			);

			$this->seedHorus();
		}

		private function seedHorus(): void {
			Horus::createPermissions( [
				User::class => '*',
			], AreasEnum::ADMIN );

			Horus::createPermissions( [
				User::class => [ 'view' ]
			], AreasEnum::USER );

			Horus::createRoles( [ RolesEnum::DEFAULT_ADMINS ], AreasEnum::ADMIN );

			Horus::createRoles( [ RolesEnum::DEFAULT_USERS ], AreasEnum::USER );

			Horus::assignPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_ADMINS, AreasEnum::ADMIN ), [
				User::class => [
					'view',
					'update'
				]
			], AreasEnum::ADMIN );

			Horus::assignPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ), [
				User::class => [ 'view' ],
			], AreasEnum::USER );


			Horus::createSuperPermissions( [
				User::class => '*',
			], AreasEnum::ADMIN );

			Horus::assignSuperPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_ADMINS, AreasEnum::ADMIN ), [
				User::class
			] );
		}

		/**
		 * Get application timezone.
		 *
		 * @param Application $app
		 *
		 * @return string|null
		 */
		protected function getApplicationTimezone( $app ) {
			return 'UTC';
		}

		/**
		 * Get package providers.
		 *
		 * @param Application $app
		 *
		 * @return array
		 */
		protected function getPackageProviders( $app ) {
			return [
				SphinxServiceProvider::class,
				HorusServiceProvider::class
			];
		}

		/**
		 * Override application aliases.
		 *
		 * @param Application $app
		 *
		 * @return array
		 */
		protected function getPackageAliases( $app ) {
			return [//	'Acme' => 'Acme\Facade',
			];
		}

		/**
		 * Define environment setup.
		 *
		 * @param Application $app
		 *
		 * @return void
		 */
		protected function defineEnvironment( $app ) {
			// Setup default database to use sqlite :memory:
			$app[ 'config' ]->set( 'database.default', 'testbench' );
			$app[ 'config' ]->set( 'database.connections.testbench', [
				'driver'   => 'sqlite',
				'database' => ':memory:',
				'prefix'   => '',
			] );
		}

		/**
		 * Define routes setup.
		 *
		 * @param Router $router
		 *
		 * @return void
		 */
		protected function defineRoutes( $router ) {
			$router->get( '/me', function() {
				return auth()->user();
			} )
			       ->middleware( 'auth:jwt' )
			       ->name( 'test.me' );
		}

		/**
		 * Define database migrations.
		 *
		 * @return void
		 */
		protected function defineDatabaseMigrations() {
			$this->loadLaravelMigrations();
		}

		/**
		 * Get base path.
		 *
		 * @return string
		 */
		protected function getBasePath() {
			return __DIR__ . '/skeleton/laravel-10.x';
		}
	}