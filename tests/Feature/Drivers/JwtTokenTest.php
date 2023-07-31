<?php

namespace Hans\Sphinx\Tests\Feature\Drivers;

    use DateTimeImmutable;
    use Hans\Sphinx\Drivers\Contracts\JwtToken;
    use Hans\Sphinx\Exceptions\SphinxException;
    use Hans\Sphinx\Tests\Factories\UserFactory;
    use Hans\Sphinx\Tests\Instances\JwtTokenInstance;
    use Hans\Sphinx\Tests\TestCase;
    use Illuminate\Database\Eloquent\Model;
    use Lcobucci\JWT\Token\Plain;

    class JwtTokenTest extends TestCase
    {
        private JwtToken $instance;
        private Model $user;

        protected function setUp(): void
        {
            parent::setUp();
            $this->instance = new JwtTokenInstance(generate_secret_key());
            $this->user = UserFactory::createNormalUser();
        }

        /**
         * @test
         *
         * @return void
         */
        public function issuedBy(): void
        {
            self::assertArrayNotHasKey('iss', $this->instance->getToken()->claims()->all());

            $this->instance->issuedBy($this->user->name);

            self::assertArrayHasKey('iss', $this->instance->getToken()->claims()->all());
            self::assertStringEqualsStringIgnoringLineEndings(
                $this->user->name,
                $this->instance->getToken()->claims()->get('iss')
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function permittedFor(): void
        {
            self::assertArrayNotHasKey('aud', $this->instance->getToken()->claims()->all());

            $this->instance->permittedFor($website = fake()->domainName());

            self::assertArrayHasKey('aud', $this->instance->getToken()->claims()->all());
            self::assertContains(
                $website,
                $this->instance->getToken()->claims()->get('aud')
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function identifiedBy(): void
        {
            self::assertArrayNotHasKey('jti', $this->instance->getToken()->claims()->all());

            $this->instance->identifiedBy($identifier = generate_secret_key());

            self::assertArrayHasKey('jti', $this->instance->getToken()->claims()->all());
            self::assertStringEqualsStringIgnoringLineEndings(
                $identifier,
                $this->instance->getToken()->claims()->get('jti')
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function canOnlyBeUsedAfter(): void
        {
            self::assertArrayNotHasKey('nbf', $this->instance->getToken()->claims()->all());

            $this->instance->canOnlyBeUsedAfter('+5 minute');

            self::assertArrayHasKey('nbf', $this->instance->getToken()->claims()->all());
            self::assertInstanceOf(
                DateTimeImmutable::class,
                $this->instance->getToken()->claims()->get('nbf')
            );

            $nbf = $this->instance->getToken()->claims()->get('nbf');
            $now = new DateTimeImmutable();

            self::assertEquals(4, $now->diff($nbf)->i);
            self::assertEquals(59, $now->diff($nbf)->s);
        }

        /**
         * @test
         *
         * @return void
         */
        public function expiresAt(): void
        {
            self::assertArrayNotHasKey('exp', $this->instance->getToken()->claims()->all());

            $this->instance->expiresAt('+5 minute');

            self::assertArrayHasKey('exp', $this->instance->getToken()->claims()->all());
            self::assertInstanceOf(
                DateTimeImmutable::class,
                $this->instance->getToken()->claims()->get('exp')
            );

            $nbf = $this->instance->getToken()->claims()->get('exp');
            $now = new DateTimeImmutable();

            self::assertEquals(4, $now->diff($nbf)->i);
            self::assertEquals(59, $now->diff($nbf)->s);
        }

        /**
         * @test
         *
         * @throws SphinxException
         *
         * @return void
         */
        public function claims(): void
        {
            $claims = [
                'G.O.A.T.'     => 'G-Eazy',
                'Tulips&Roses' => 'Nothing ever last for ever.',
            ];

            self::assertArrayNotHasKey(
                array_keys($claims)[0],
                $this->instance->getToken()->claims()->all()
            );
            self::assertArrayNotHasKey(
                array_keys($claims)[1],
                $this->instance->getToken()->claims()->all()
            );

            $this->instance->claims($claims);

            self::assertArrayHasKey(
                array_keys($claims)[0],
                $this->instance->getToken()->claims()->all()
            );
            self::assertArrayHasKey(
                array_keys($claims)[1],
                $this->instance->getToken()->claims()->all()
            );

            self::assertStringEqualsStringIgnoringLineEndings(
                $claims[array_keys($claims)[0]],
                $this->instance->getToken()->claims()->get(array_keys($claims)[0])
            );
            self::assertStringEqualsStringIgnoringLineEndings(
                $claims[array_keys($claims)[1]],
                $this->instance->getToken()->claims()->get(array_keys($claims)[1])
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function claim(): void
        {
            $key = 'DontLetMeGo';
            $value = 'Even the brightest color turns gray.';
            self::assertArrayNotHasKey(
                $key,
                $this->instance->getToken()->claims()->all()
            );

            $this->instance->claim($key, $value);

            self::assertArrayHasKey(
                $key,
                $this->instance->getToken()->claims()->all()
            );

            self::assertStringEqualsStringIgnoringLineEndings(
                $value,
                $this->instance->getToken()->claims()->get($key)
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function claimWhen(): void
        {
            $key = 'LifeStyleOfReachAndHated';
            $value = fn () => 'No one believed in me at first. You just laughed and said i hope it works.';
            self::assertArrayNotHasKey(
                $key,
                $this->instance->getToken()->claims()->all()
            );

            $this->instance->claimWhen(true, $key, $value);

            self::assertArrayHasKey(
                $key,
                $this->instance->getToken()->claims()->all()
            );

            self::assertStringEqualsStringIgnoringLineEndings(
                $value(),
                $this->instance->getToken()->claims()->get($key)
            );
        }

        /**
         * @test
         *
         * @throws SphinxException
         *
         * @return void
         */
        public function headers(): void
        {
            $headers = [
                'G-Eazy'   => 'I bring up facts, You bring us boll shit, you should relax.',
                'G.O.A.T.' => 'Life can be worse, What can i do but enjoy the perks?',
            ];

            self::assertArrayNotHasKey(
                array_keys($headers)[0],
                $this->instance->getToken()->headers()->all()
            );
            self::assertArrayNotHasKey(
                array_keys($headers)[1],
                $this->instance->getToken()->headers()->all()
            );

            $this->instance->headers($headers);

            self::assertArrayHasKey(
                array_keys($headers)[0],
                $this->instance->getToken()->headers()->all()
            );
            self::assertArrayHasKey(
                array_keys($headers)[1],
                $this->instance->getToken()->headers()->all()
            );

            self::assertStringEqualsStringIgnoringLineEndings(
                $headers[array_keys($headers)[0]],
                $this->instance->getToken()->headers()->get(array_keys($headers)[0])
            );
            self::assertStringEqualsStringIgnoringLineEndings(
                $headers[array_keys($headers)[1]],
                $this->instance->getToken()->headers()->get(array_keys($headers)[1])
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function header(): void
        {
            $key = 'HadEnough';
            $value = 'Watch out, remember CARMA\'s real. That bull shit comes back to bite you.';
            self::assertArrayNotHasKey(
                $key,
                $this->instance->getToken()->headers()->all()
            );

            $this->instance->header($key, $value);

            self::assertArrayHasKey(
                $key,
                $this->instance->getToken()->headers()->all()
            );

            self::assertStringEqualsStringIgnoringLineEndings(
                $value,
                $this->instance->getToken()->headers()->get($key)
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function headerWhen(): void
        {
            $key = 'Spectacular now';
            $value = fn () => 'See all the things i don\'t like in me, i can\'t bear my self.';
            self::assertArrayNotHasKey(
                $key,
                $this->instance->getToken()->headers()->all()
            );

            $this->instance->headerWhen(true, $key, $value);

            self::assertArrayHasKey(
                $key,
                $this->instance->getToken()->headers()->all()
            );

            self::assertStringEqualsStringIgnoringLineEndings(
                $value(),
                $this->instance->getToken()->headers()->get($key)
            );
        }

        /**
         * @test
         *
         * @return void
         */
        public function encode(): void
        {
            self::assertArrayNotHasKey(
                'iat',
                $this->instance->getToken()->claims()->all()
            );

            $this->instance->encode();

            self::assertArrayHasKey(
                'iat',
                $this->instance->getToken()->claims()->all()
            );
            self::assertInstanceOf(
                DateTimeImmutable::class,
                $this->instance->getToken()->claims()->get('iat')
            );
            self::assertEquals(
                0,
                ( new DateTimeImmutable() )->diff($this->instance->getToken()->claims()->get('iat'))->s
            );
        }

        /**
         * @test
         *
         * @throws SphinxException
         *
         * @return void
         */
        public function decode(): void
        {
            $encoded = $this->instance
                ->claim($claimKey = 'Ruthless', $claimValue = 'Gave you my heart and you fuck around and broke that.')
                ->header($headerKey = 'Him&I', $headerValue = 'Cross my heart, I hope to die.')
                ->encode()
                ->getToken()
                ->toString();

            $decoded = $this->instance->decode($encoded);

            self::assertArrayHasKey(
                $claimKey,
                $decoded->claims()->all()
            );
            self::assertStringEqualsStringIgnoringLineEndings(
                $claimValue,
                $decoded->claims()->get($claimKey)
            );

            self::assertArrayHasKey(
                $headerKey,
                $decoded->headers()->all()
            );
            self::assertStringEqualsStringIgnoringLineEndings(
                $headerValue,
                $decoded->headers()->get($headerKey)
            );
        }

        /**
         * @test
         *
         * @throws SphinxException
         *
         * @return void
         */
        public function assert(): void
        {
            $token = $this->instance
                ->expiresAt()
                ->encode()
                ->getToken()
                ->toString();

            $this->instance->assert($token);

            $token = $this->instance
                ->expiresAt('-1 second')
                ->encode()
                ->getToken()
                ->toString();

            $this->expectException(SphinxException::class);
            $this->expectExceptionMessage('Token expired!');

            $this->instance->assert($token);
        }

        /**
         * @test
         *
         * @return void
         */
        public function validate(): void
        {
            $token = $this->instance
                ->expiresAt()
                ->encode()
                ->getToken()
                ->toString();

            self::assertTrue($this->instance->validate($token));

            $token = $this->instance
                ->expiresAt('-1 second')
                ->encode()
                ->getToken()
                ->toString();

            self::assertFalse($this->instance->validate($token));
        }

        /**
         * @test
         *
         * @return void
         */
        public function getToken(): void
        {
            self::assertInstanceOf(
                Plain::class,
                $this->instance->encode()->getToken()
            );
        }
    }
