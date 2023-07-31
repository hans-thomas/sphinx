<?php

namespace Hans\Sphinx\Tests\Feature\Services;

use Hans\Horus\Exceptions\HorusException;
use Hans\Sphinx\Exceptions\SphinxException;
use Hans\Sphinx\Facades\Sphinx;
use Hans\Sphinx\Services\SphinxService;
use Hans\Sphinx\Tests\Factories\UserFactory;
use Hans\Sphinx\Tests\TestCase;
use Lcobucci\JWT\UnencryptedToken;

use function PHPUnit\Framework\assertStringEqualsStringIgnoringLineEndings;

class SphinxServiceTest extends TestCase
{
    /**
     * @test
     *
     * @throws HorusException
     * @throws SphinxException
     *
     * @return void
     */
    public function decode(): void
    {
        $token = UserFactory::generateToken()->getAccessToken();

        self::assertInstanceOf(
            UnencryptedToken::class,
            $decoded = Sphinx::decode($token)
        );

        self::assertIsString($decoded->toString());
        self::assertStringEqualsStringIgnoringLineEndings(
            $decoded->toString(),
            $token
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function generateTokenFor(): void
    {
        $user = UserFactory::createNormalUser();
        $instance = Sphinx::generateTokenFor($user);

        self::assertInstanceOf(
            SphinxService::class,
            $instance
        );
        self::assertIsString($instance->getAccessToken());
        self::assertIsString($instance->getRefreshToken());
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function getAccessToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)->getAccessToken();

        self::assertIsString($token);

        $decoded = Sphinx::decode($token);

        self::assertTrue($decoded->claims()->has('_token'));
        self::assertStringEqualsStringIgnoringLineEndings(
            Sphinx::getInnerAccessToken($token)->toString(),
            $decoded->claims()->get('_token')
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function getRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)->getRefreshToken();

        self::assertIsString($token);

        $decoded = Sphinx::decode($token);

        self::assertTrue($decoded->claims()->has('_token'));
        self::assertStringEqualsStringIgnoringLineEndings(
            Sphinx::getInnerRefreshToken($token)->toString(),
            $decoded->claims()->get('_token')
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function claim(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->claim('new', 'test')
                       ->getAccessToken();

        self::assertFalse(Sphinx::decode($token)->claims()->has('new'));
        self::assertTrue(Sphinx::getInnerAccessToken($token)->claims()->has('new'));

        assertStringEqualsStringIgnoringLineEndings(
            'test',
            Sphinx::getInnerAccessToken($token)->claims()->get('new')
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function header(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->header('new', 'test')
                       ->getAccessToken();

        self::assertFalse(Sphinx::decode($token)->headers()->has('new'));
        self::assertTrue(Sphinx::getInnerAccessToken($token)->headers()->has('new'));

        assertStringEqualsStringIgnoringLineEndings(
            'test',
            Sphinx::getInnerAccessToken($token)->headers()->get('new')
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function validateWrapperAccessToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getAccessToken();

        self::assertTrue(Sphinx::validateWrapperAccessToken($token));

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;
        self::assertFalse(Sphinx::validateWrapperAccessToken($token));
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function assertWrapperAccessToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getAccessToken();

        Sphinx::assertWrapperAccessToken($token);

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;

        $this->expectException(SphinxException::class);

        Sphinx::assertWrapperAccessToken($token);
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function validateInnerAccessToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getAccessToken();

        self::assertTrue(Sphinx::validateInnerAccessToken($token));

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;

        self::assertFalse(Sphinx::validateInnerAccessToken($token));
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function assertInnerAccessToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getAccessToken();

        Sphinx::assertWrapperAccessToken($token);

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;

        $this->expectException(SphinxException::class);

        Sphinx::assertWrapperAccessToken($token);
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function getInnerAccessToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getAccessToken();

        $inner = Sphinx::getInnerAccessToken($token);
        self::assertStringEqualsStringIgnoringLineEndings(
            Sphinx::decode($token)->claims()->get('_token'),
            $inner->toString()
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function validateWrapperRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getRefreshToken();

        self::assertTrue(Sphinx::validateWrapperRefreshToken($token));

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;
        self::assertFalse(Sphinx::validateWrapperRefreshToken($token));
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function assertWrapperRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getRefreshToken();

        Sphinx::assertWrapperRefreshToken($token);

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;

        $this->expectException(SphinxException::class);

        Sphinx::assertWrapperRefreshToken($token);
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function validateInnerRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getRefreshToken();

        self::assertTrue(Sphinx::validateInnerRefreshToken($token));

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;

        self::assertFalse(Sphinx::validateInnerRefreshToken($token));
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function assertInnerRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getRefreshToken();

        Sphinx::assertInnerRefreshToken($token);

        $index = rand(0, strlen($token) - 1);
        do {
            $randomAlphabet = fake()->word()[0];
        } while ($token[$index] == $randomAlphabet);
        $token[$index] = $randomAlphabet;

        $this->expectException(SphinxException::class);

        Sphinx::assertInnerRefreshToken($token);
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function getInnerRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getRefreshToken();

        $inner = Sphinx::getInnerRefreshToken($token);
        self::assertStringEqualsStringIgnoringLineEndings(
            Sphinx::decode($token)->claims()->get('_token'),
            $inner->toString()
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function getPermissions(): void
    {
        $user = UserFactory::createNormalUser();
        $token = Sphinx::generateTokenFor($user)
                       ->getAccessToken();

        self::assertCount(
            $user->getAllPermissions()->count(),
            Sphinx::getPermissions($token)
        );
        self::assertEquals(
            $user->getAllPermissions()->pluck('name', 'id')->toArray(),
            Sphinx::getPermissions($token)
        );
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function isRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $instance = Sphinx::generateTokenFor($user);
        $access = $instance->getAccessToken();
        $refresh = $instance->getRefreshToken();

        self::assertFalse(Sphinx::isRefreshToken($access));
        self::assertTrue(Sphinx::isRefreshToken($refresh));
    }

    /**
     * @test
     *
     * @throws HorusException
     *
     * @return void
     */
    public function isNotRefreshToken(): void
    {
        $user = UserFactory::createNormalUser();
        $instance = Sphinx::generateTokenFor($user);
        $access = $instance->getAccessToken();
        $refresh = $instance->getRefreshToken();

        self::assertTrue(Sphinx::isNotRefreshToken($access));
        self::assertFalse(Sphinx::isNotRefreshToken($refresh));
    }

    /**
     * @test
     *
     * @throws HorusException
     * @throws SphinxException
     *
     * @return void
     */
    public function getCurrentSessionAndGuessSession(): void
    {
        $user = UserFactory::createNormalUser();
        $access = ( new SphinxService() )->generateTokenFor($user)->getAccessToken();

        request()->headers->set('Authorization', "Bearer $access");

        self::assertEquals(
            $user->sessions()->first()->toArray(),
            ( new SphinxService() )->getCurrentSession()->toArray()
        );
    }
}
