<?php

	namespace Hans\Sphinx\Tests\Feature\Traits;

	use App\Models\User;
	use AreasEnum;
	use Hans\Horus\Exceptions\HorusException;
	use Hans\Horus\Models\Permission;
	use Hans\Sphinx\Facades\Sphinx;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;

	class SphinxTokenCanTest extends TestCase {

		private User $user;

		protected function setUp(): void {
			parent::setUp();
			$this->user = UserFactory::createAdminUser();
			request()->headers->set(
				'Authorization', 'Bearer ' . Sphinx::generateTokenFor( $this->user )->getAccessToken()
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws HorusException
		 */
		public function can(): void {
			self::assertTrue(
				$this->user->can( 'models-user-view' )
			);
			self::assertTrue(
				$this->user->can( Permission::findByName( 'models-user-view', AreasEnum::ADMIN )->id )
			);
			self::assertTrue(
				$this->user->can( 'models-user-*' )
			);

			self::assertFalse(
				$this->user->can( 'wrong-view' )
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws HorusException
		 */
		public function canAny(): void {
			self::assertTrue(
				$this->user->canAny( [ 'wrong-update', 'models-user-view', 'wrong-view' ] )
			);
			self::assertTrue(
				$this->user->canAny( [ 'wrong-update', 'models-user-*', 'wrong-view' ] )
			);
			self::assertTrue(
				$this->user->canAny( Permission::findByName( 'models-user-view', AreasEnum::ADMIN )->id )
			);

			self::assertFalse(
				$this->user->canAny( [ 'wrong-view', 'wrong-update' ] )
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function cannot(): void {
			self::assertTrue(
				$this->user->cannot( 'wrong-update' )
			);
			self::assertTrue(
				$this->user->cannot( [ 'wrong-view', 'wrong-update' ] )
			);

			self::assertFalse(
				$this->user->cannot( 'models-user-view' )
			);
			self::assertFalse(
				$this->user->cannot( 'models-user-*' )
			);
			self::assertFalse(
				$this->user->cannot( [ 'models-user-view', 'models-user-*' ] )
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function cant(): void {
			self::assertTrue(
				$this->user->cant( 'wrong-update' )
			);
			self::assertTrue(
				$this->user->cant( [ 'wrong-view', 'wrong-update' ] )
			);

			self::assertFalse(
				$this->user->cant( 'models-user-view' )
			);
			self::assertFalse(
				$this->user->cant( 'models-user-*' )
			);
			self::assertFalse(
				$this->user->cant( [ 'models-user-view', 'models-user-*' ] )
			);
		}

	}