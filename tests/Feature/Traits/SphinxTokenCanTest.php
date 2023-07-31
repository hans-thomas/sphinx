<?php

namespace Hans\Sphinx\Tests\Feature\Traits;

use App\Models\User;
use Hans\Sphinx\Facades\Sphinx;
use Hans\Sphinx\Tests\Factories\UserFactory;
use Hans\Sphinx\Tests\TestCase;
use Spatie\Permission\Models\Permission;

class SphinxTokenCanTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserFactory::createAdminUser();
        request()->headers->set(
            'Authorization',
            'Bearer '.Sphinx::generateTokenFor($this->user)->getAccessToken()
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function can(): void
    {
        self::assertTrue(
            $this->user->can('user-view')
        );
        self::assertTrue(
            $this->user->can(Permission::findByName('user-view')->id)
        );
        self::assertTrue(
            $this->user->can('user-*')
        );

        self::assertFalse(
            $this->user->can('wrong-view')
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function canAny(): void
    {
        self::assertTrue(
            $this->user->canAny(['wrong-update', 'user-view', 'wrong-view'])
        );
        self::assertTrue(
            $this->user->canAny(['wrong-update', 'user-*', 'wrong-view'])
        );
        self::assertTrue(
            $this->user->canAny(Permission::findByName('user-view')->id)
        );

        self::assertFalse(
            $this->user->canAny(['wrong-view', 'wrong-update'])
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function cannot(): void
    {
        self::assertTrue(
            $this->user->cannot('wrong-update')
        );
        self::assertTrue(
            $this->user->cannot(['wrong-view', 'wrong-update'])
        );

        self::assertFalse(
            $this->user->cannot('user-view')
        );
        self::assertFalse(
            $this->user->cannot('user-*')
        );
        self::assertFalse(
            $this->user->cannot(['user-view', 'user-*'])
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function cant(): void
    {
        self::assertTrue(
            $this->user->cant('wrong-update')
        );
        self::assertTrue(
            $this->user->cant(['wrong-view', 'wrong-update'])
        );

        self::assertFalse(
            $this->user->cant('user-view')
        );
        self::assertFalse(
            $this->user->cant('user-*')
        );
        self::assertFalse(
            $this->user->cant(['user-view', 'user-*'])
        );
    }
}
