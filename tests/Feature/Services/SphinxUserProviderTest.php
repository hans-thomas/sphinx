<?php

	namespace Hans\Sphinx\Tests\Feature\Services;

	use App\Models\User;
	use Hans\Sphinx\Facades\Sphinx;
	use Hans\Sphinx\Services\SphinxUserProvider;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;

	class SphinxUserProviderTest extends TestCase {

		private User $user;
		private SphinxUserProvider $provider;

		protected function setUp(): void {
			parent::setUp();
			$this->user     = UserFactory::createNormalUser();
			$this->provider = app(
				SphinxUserProvider::class,
				[ 'model' => User::class ]
			);
		}


		/**
		 * @test
		 *
		 * @return void
		 */
		public function retrieveByToken(): void {
			self::assertEquals(
				$this->user->withoutRelations()->toArray(),
				$this->provider->retrieveByToken( $this->user->id )->toArray()
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function updateRememberToken(): void {
			self::assertNull(
				$this->provider->updateRememberToken()
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function retrieveByJwtTokenCredentials(): void {
			Sphinx::generateTokenFor( $this->user )->getAccessToken();

			self::assertEquals(
				$this->user->only( 'id', 'email' ),
				$this->provider
					->retrieveByJwtTokenCredentials(
						[ 'id' => $this->user->id, 'email' => $this->user->email ]
					)
					->toArray()
			);
		}

	}