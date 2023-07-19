<?php


	namespace Hans\Sphinx\Tests\Factories;


	use App\Models\User;
	use Hans\Horus\Exceptions\HorusException;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Facades\Sphinx;
	use Hans\Sphinx\Services\SphinxService;
	use Hans\Sphinx\Tests\TestCase;

	class UserFactory {

		/**
		 * @return User
		 */
		public static function creatWithoutRole(): User {
			$user = User::factory()->create();

			return $user->fresh();
		}

		/**
		 * @return User
		 * @throws HorusException
		 */
		public static function createNormalUser(): User {
			$user = User::factory()->create();
			$user->assignRole( TestCase::DEFAULT_USERS );

			return $user->fresh();
		}

		/**
		 * @return User
		 * @throws HorusException
		 * @throws SphinxException
		 */
		public static function createNormalUserWithSession(): User {
			$user = User::factory()->create();
			$user->assignRole( TestCase::DEFAULT_USERS );
			capture_session( $user );

			return $user->fresh();
		}

		/**
		 * @return User
		 * @throws HorusException
		 */
		public static function createAdminUser(): User {
			$user = User::factory()->create();
			$user->assignRole( TestCase::DEFAULT_ADMINS );

			return $user->fresh();
		}

		/**
		 * @param User|null $user
		 *
		 * @return SphinxService
		 * @throws HorusException
		 * @throws SphinxException
		 */
		public static function generateToken( User $user = null ): SphinxService {
			if ( is_null( $user ) ) {
				$user = self::createNormalUserWithSession();
			}

			return Sphinx::generateTokenFor( $user );
		}

	}
