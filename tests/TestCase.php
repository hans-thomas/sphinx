<?php

namespace Hans\Sphinx\Tests;

use App\Models\RoleDelegate;
use App\Models\User;
use Exception;
use Hans\Horus\Facades\Horus;
use Hans\Horus\HorusServiceProvider;
use Hans\Sphinx\SphinxServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
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

        config()->set('cache.default', 'file');
        config()->set(
            'sphinx.secret',
            'XELnlAjESvqWDS3utBoN9cEA8eF3PlTtyXJ1OmCUIhxfIJKdePkoof8aKCbfucOCqpuygSDv4ZobA4936UXqzshfJrw'
        );
        config()->set('sphinx.role_model', RoleDelegate::class);
        config()->set('permission.models.role', RoleDelegate::class);

        $this->seedHorus();

        Cache::clear();
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
        $version = $this->getPackageVersion('orchestra/testbench');
        if (version_compare($version, '9.0', '>=')) {
            $this->loadMigrationsFrom(__DIR__.'/skeleton/laravel-11.x/database/migrations');
        } elseif (version_compare($version, '8.0', '>=')) {
            $this->loadMigrationsFrom(__DIR__.'/skeleton/laravel-10.x/migrations');
        } else {
            $this->loadLaravelMigrations();
        }
    }

    /**
     * Get base path.
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getBasePath(): string
    {
        $version = $this->getPackageVersion('orchestra/testbench');

        if (version_compare($version, '9.0', '>=')) {
            return __DIR__.'/skeleton/laravel-11.x';
        } elseif (version_compare($version, '8.0', '>=')) {
            return __DIR__.'/skeleton/laravel-10.x';
        }

        return parent::getBasePath();
    }

    /**
     * @throws Exception
     */
    private function getPackageVersion(string $name): string
    {
        $version = null;
        $lockFileContent = json_decode(file_get_contents(__DIR__.'/../composer.lock'), true);

        foreach ($lockFileContent['packages'] as $package) {
            if ($package['name'] === $name) {
                $version = $package['version'];
            }
        }

        foreach ($lockFileContent['packages-dev'] as $package) {
            if ($package['name'] === $name) {
                $version = $package['version'];
            }
        }

        if ($version !== null) {
            return str_replace('v', '', $version);
        }

        throw new Exception('Package '.$name.' not installed');
    }
}
