<?php

namespace Hans\Sphinx\Tests;

    use App\Models\RoleDelegate;
    use App\Models\User;
    use Hans\Horus\Facades\Horus;
    use Hans\Horus\HorusServiceProvider;
    use Hans\Sphinx\SphinxServiceProvider;
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Routing\Router;
    use Orchestra\Testbench\TestCase as BaseTestCase;
    use Spatie\Permission\PermissionServiceProvider;

    class TestCase extends BaseTestCase
    {
        use RefreshDatabase;

        const ADMIN_AREA = 'employees';
        const DEFAULT_ADMINS = 'admin';

        const USER_AREA = 'customers';
        const DEFAULT_USERS = 'user';

        /**
         * Setup the test environment.
         */
        protected function setUp(): void
        {
            parent::setUp();
            config()->set('cache.default', 'array');
            config()->set(
                'sphinx.secret',
                'XELnlAjESvqWDS3utBoN9cEA8eF3PlTtyXJ1OmCUIhxfIJKdePkoof8aKCbfucOCqpuygSDv4ZobA4936UXqzshfJrw'
            );
            config()->set(
                'sphinx.role_model',
                RoleDelegate::class
            );

            $this->seedHorus();
        }

        private function seedHorus(): void
        {
            Horus::createPermissions([User::class]);

            Horus::createSuperPermissions([User::class]);

            Horus::createRoles([self::DEFAULT_ADMINS, self::DEFAULT_USERS]);

            Horus::assignPermissionsToRole(
                self::DEFAULT_ADMINS,
                [
                    User::class => [
                        'view',
                        'update',
                    ],
                ]
            );

            Horus::assignPermissionsToRole(self::DEFAULT_USERS, [User::class => ['view']]);

            Horus::assignSuperPermissionsToRole(self::DEFAULT_ADMINS, [User::class]);
        }

        /**
         * Get application timezone.
         *
         * @param Application $app
         *
         * @return string|null
         */
        protected function getApplicationTimezone($app): ?string
        {
            return 'UTC';
        }

        /**
         * Get package providers.
         *
         * @param Application $app
         *
         * @return array
         */
        protected function getPackageProviders($app): array
        {
            return [
                SphinxServiceProvider::class,
                HorusServiceProvider::class,
                PermissionServiceProvider::class,
            ];
        }

        /**
         * Override application aliases.
         *
         * @param Application $app
         *
         * @return array
         */
        protected function getPackageAliases($app): array
        {
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
        protected function defineEnvironment($app): void
        {
            // Setup default database to use sqlite :memory:
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
        }

        /**
         * Define routes setup.
         *
         * @param Router $router
         *
         * @return void
         */
        protected function defineRoutes($router): void
        {
            $router->get('/me', function () {
                return auth()->user();
            })
                   ->name('test.me');
        }

        /**
         * Define database migrations.
         *
         * @return void
         */
        protected function defineDatabaseMigrations(): void
        {
            $this->loadLaravelMigrations();
        }

        /**
         * Get base path.
         *
         * @return string
         */
        protected function getBasePath(): string
        {
            return __DIR__.'/skeleton/laravel-10.x';
        }
    }
