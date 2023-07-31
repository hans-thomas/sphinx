<?php

namespace Hans\Sphinx\Tests\Feature\Services;

    use Hans\Horus\Exceptions\HorusException;
    use Hans\Sphinx\Facades\Sphinx;
    use Hans\Sphinx\Services\SphinxGuard;
    use Hans\Sphinx\Services\SphinxUserProvider;
    use Hans\Sphinx\Tests\Factories\UserFactory;
    use Hans\Sphinx\Tests\TestCase;
    use Illuminate\Database\Eloquent\Model;

    class SphinxGuardTest extends TestCase
    {
        private SphinxGuard $guard;
        private Model $user;

        protected function setUp(): void
        {
            parent::setUp();
            $this->user = UserFactory::createNormalUser();
            request()->headers
                ->set(
                    'Authorization',
                    'Bearer '.Sphinx::generateTokenFor($this->user)->getAccessToken()
                );
            $this->guard = app(SphinxGuard::class, [
                'provider' => app(
                    SphinxUserProvider::class,
                    ['model' => $this->app['config']['auth.providers.sphinxUsers.model']]
                ),
            ]);
        }

        /**
         * @test
         *
         * @return void
         */
        public function getAuthIdentifierName(): void
        {
            self::assertEquals(
                $this->user->getKeyName(),
                $this->guard->getAuthIdentifierName()
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function getAuthIdentifier(): void
        {
            self::assertEquals(
                $this->user->id,
                $this->guard->getAuthIdentifier()
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function getAuthPassword(): void
        {
            self::assertEquals(
                $this->user->password,
                $this->guard->getAuthPassword()
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function getRememberToken(): void
        {
            self::assertNull($this->guard->getRememberToken());
        }

        /**
         * @test
         *
         * @return void
         */
        public function setRememberToken(): void
        {
            self::assertNull($this->guard->setRememberToken());
        }

        /**
         * @test
         *
         * @return void
         */
        public function getRememberTokenName(): void
        {
            self::assertNull($this->guard->getRememberTokenName());
        }

        /**
         * @test
         *
         * @return void
         */
        public function user(): void
        {
            self::assertEquals(
                $this->user->only('id', 'name', 'email', 'version'),
                $this->guard->user()->toArray()
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function attempt(): void
        {
            self::assertTrue(
                $this->guard->attempt(['id' => $this->user->id, 'password' => 'password'])
            );
            self::assertEquals(
                $this->user->withoutRelations()->toArray(),
                $this->guard->user()->toArray()
            );

            self::assertFalse(
                $this->guard->attempt(['id' => $this->user->id + 512, 'password' => 'password'])
            );
            self::assertFalse(
                $this->guard->attempt(['id' => $this->user->id, 'password' => 'wrong'])
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function loginUsingId(): void
        {
            self::assertEquals(
                $this->user->withoutRelations()->toArray(),
                $this->guard->loginUsingId($this->user->id)->toArray()
            );

            self::assertEquals(
                null,
                $this->guard->loginUsingId($this->user->id + 512)
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function validate(): void
        {
            self::assertTrue(
                $this->guard->validate(['id' => $this->user->id, 'password' => 'password'])
            );
            self::assertTrue(
                $this->guard->validate(['email' => $this->user->email, 'password' => 'password'])
            );

            self::assertFalse(
                $this->guard->validate(['id' => $this->user->id + 512, 'password' => 'password'])
            );
            self::assertFalse(
                $this->guard->validate(['email' => fake()->email(), 'password' => 'password'])
            );
            self::assertFalse(
                $this->guard->validate(['email' => $this->user->email, 'password' => 'wrong'])
            );
        }

        /**
         * @test
         *
         * @throws HorusException
         *
         * @return void
         */
        public function login(): void
        {
            $user = UserFactory::createNormalUser();
            $this->guard->login($user);

            self::assertEquals(
                $user->withoutRelations()->toArray(),
                $this->guard->user()->toArray()
            );
        }

        /**
         * @test
         *
         * @throws HorusException
         *
         * @return void
         */
        public function loginUsingToken(): void
        {
            $user = UserFactory::createNormalUser();
            $token = Sphinx::generateTokenFor($user)->getAccessToken();

            $this->guard->loginUsingToken($token);
            self::assertEquals(
                $user->withoutRelations()->only('id', 'name', 'email', 'version'),
                $this->guard->user()->toArray()
            );
        }
    }
