<?php

namespace Hans\Sphinx\Tests\Feature\Traits;

    use App\Models\User;
    use Hans\Horus\Exceptions\HorusException;
    use Hans\Sphinx\Exceptions\SphinxException;
    use Hans\Sphinx\Helpers\Enums\SphinxCache;
    use Hans\Sphinx\Tests\Factories\UserFactory;
    use Hans\Sphinx\Tests\TestCase;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Cache;

    class SphinxMethodsTest extends TestCase
    {
        private Model $user;

        protected function setUp(): void
        {
            parent::setUp();
            $this->user = UserFactory::createNormalUser();
        }

        /**
         * @test
         *
         * @return void
         */
        public function increaseVersionUsingNoSession(): void
        {
            $version = $this->user->getVersion();

            $this->user->update(['name' => fake()->name()]);

            self::assertEquals(
                $version, // no change
                $this->user->getVersion()
            );
        }

        /**
         * @test
         *
         * @throws HorusException|SphinxException
         *
         * @return void
         */
        public function increaseVersionUsingSession(): void
        {
            capture_session($this->user);

            $version = $this->user->getVersion();
            $session = $this->user->sessions->first();
            $key = SphinxCache::SESSION.$session->id;

            self::assertEquals(
                $version,
                Cache::get($key)->sessionable_version
            );

            $this->user->update(['name' => fake()->name()]);

            self::assertEquals(
                $version + 1,
                $this->user->getVersion()
            );
            self::assertEquals(
                $version + 1,
                Cache::get($key)->sessionable_version
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function getVersion(): void
        {
            self::assertEquals(
                $this->user->version,
                $this->user->getVersion()
            );

            $user = User::query()->find($this->user->id, ['id']);

            self::assertEquals(
                $this->user->getVersion(),
                $user->getVersion()
            );
        }
    }
