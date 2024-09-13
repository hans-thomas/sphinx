<?php

namespace Hans\Sphinx\Tests\Feature;

use App\Models\User;
use Hans\Sphinx\Facades\Sphinx;
use Hans\Sphinx\Tests\Factories\UserFactory;
use Hans\Sphinx\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class PerformanceTest extends TestCase
{
    private User $user;

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
    public function noQueryDuringAuthenticatingUsingToken(): void
    {
        $token = Sphinx::generateTokenFor($this->user)->getAccessToken();

        DB::enableQueryLog();
        $this->getJson(
            uri: route('test.me'),
            headers: [
                'Authorization' => "Bearer $token",
            ]
        );
        $this->getJson(
            uri: route('test.me'),
            headers: [
                'Authorization' => "Bearer $token",
            ]
        );
        $this->getJson(
            uri: route('test.me'),
            headers: [
                'Authorization' => "Bearer $token",
            ]
        );

        self::assertCount(
            1, // Query for the user's role on the first request, then it caches the role
            DB::getQueryLog()
        );
    }
}
