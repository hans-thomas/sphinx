<?php

	namespace Hans\Sphinx\Tests\Feature;

	use App\Models\User;
	use AreasEnum;
	use Hans\Horus\Models\Permission;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;
	use Illuminate\Support\Arr;

	class JwtTokenCanTest extends TestCase {
		private User $user;
		private string $token;
		private Permission $normalPermission;
		private Permission $adminPermission;

		/**
		 * Setup the test environment.
		 *
		 * @return void
		 */
		protected function setUp(): void {
			parent::setUp();
			$this->user  = UserFactory::createNormalUserWithSession();
			$this->token = UserFactory::createAccessToken( $this->user );

			$this->normalPermission = Permission::firstWhere( [
				'name' => 'user-view',
				'area' => AreasEnum::USER
			] );
			$this->adminPermission  = Permission::whereNotIn( 'name',
				Permission::where( 'area', AreasEnum::USER )->get()->pluck( 'name' )->toArray() )->first();

		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function tokenCan() {
			request()->headers->set( 'Authorization', 'Bearer ' . $this->token );

			$this->assertTrue( $this->user->can( $this->normalPermission->name ) );
			$this->assertTrue( $this->user->can( $this->normalPermission->id ) );
			$this->assertFalse( $this->user->can( $this->adminPermission->name ) );
			$this->assertFalse( $this->user->can( $this->adminPermission->id ) );
			$this->assertFalse( $this->user->can( 'non-exists-permission' ) );
			$this->assertFalse( $this->user->can( 999 ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function tokenCanAny() {
			request()->headers->set( 'Authorization', 'Bearer ' . $this->token );

			$this->assertTrue( $this->user->can( $this->normalPermission->name ) );
			$this->assertFalse( $this->user->can( $this->adminPermission->name ) );
			$this->assertTrue( $this->user->canAny( [ $this->normalPermission->name, $this->adminPermission->id ] ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function tokenCant() {
			request()->headers->set( 'Authorization', 'Bearer ' . $this->token );

			$this->assertFalse( $this->user->can( $this->adminPermission->name ) );
			$this->assertTrue( $this->user->cannot( $this->adminPermission->name ) );
			$this->assertTrue( $this->user->cant( $this->adminPermission->name ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function tokenSuperPermissions() {
			$token = $this->sphinx->session( $session = $this->user->sessions->last() )
			                      ->claim( 'permissions',
				                      array_merge( $this->user->getAllPermissions()->pluck( 'name', 'id' )->toArray(), [
					                      '*-*'
				                      ] ) )
			                      ->accessToken();

			request()->headers->set( 'Authorization', 'Bearer ' . $token );

			$this->assertTrue( $this->user->can( $this->adminPermission->name ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function tokenSuperPermissionsForModel() {
			$model = Arr::first( explode( '-', $this->adminPermission->name ) );
			$token = $this->sphinx->session( $session = $this->user->sessions->last() )
			                      ->claim( 'permissions',
				                      array_merge( $this->user->getAllPermissions()->pluck( 'name', 'id' )->toArray(), [
					                      $model . '-*'
				                      ] ) )
			                      ->accessToken();

			request()->headers->set( 'Authorization', 'Bearer ' . $token );

			$this->assertTrue( $this->user->can( $this->adminPermission->name ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function tokenExpired() {
			$this->user->update( [ 'name' => 'updated name' ] ); // increment the version
			$this->getJson( route( 'test.me' ), headers: [
				'Authorization' => 'Bearer ' . $this->token
			] )->assertForbidden()->assertJson( [
				"code"   => "1006",
				"detail" => "User's token is out-of-date!",
				"title"  => "Unexpected error!"
			] );

		}

	}
