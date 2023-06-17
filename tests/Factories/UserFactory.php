<?php


	namespace Hans\Sphinx\Tests\Factories;


	use App\Models\User;
	use AreasEnum;
	use Hans\Horus\Models\Role;
	use Hans\Sphinx\Facades\Sphinx;
	use RolesEnum;

	class UserFactory {

		public static function createAUser(): User {
			$user = User::factory()->create();
			$user->assignRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ) );

			return $user->fresh();
		}

		public static function createAUserWithoutRole(): User {
			$user = User::factory()->create();

			return $user->fresh();
		}

		public static function createNormalUserWithSession(): User {
			$user = User::factory()->create();
			$user->assignRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ) );
			$user->sessions()->create( [
				'ip'       => '127.0.0.' . rand( 0, 255 ),
				'device'   => 'Nokia 5.3',
				'platform' => 'Android 11',
				'secret' => \Illuminate\Support\Str::random( 64 )
			] );

			return $user->fresh();
		}

		public static function createAccessToken( User $user ): string {
			return Sphinx::generateTokenFor( $user )->getAccessToken();
		}

		public static function createRefreshToken( User $user ): string {
			return Sphinx::generateTokenFor( $user )->getRefreshToken();
		}
	}
