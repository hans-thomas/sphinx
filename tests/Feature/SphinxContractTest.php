<?php

	namespace Hans\Sphinx\Tests\Feature;

	use App\Models\User;
	use Hans\Sphinx\Contracts\SphinxContract;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;
	use Lcobucci\JWT\UnencryptedToken;

	class SphinxContractTest extends TestCase {

		private User $user;
		private Session $session;
		private string $token;

		/**
		 * Setup the test environment.
		 *
		 * @return void
		 */
		protected function setUp(): void {
			parent::setUp();
			$this->user    = UserFactory::createNormalUserWithSession();
			$this->session = $this->user->sessions()->latest()->first();
			$this->token   = UserFactory::createAccessToken( $this->user );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function unauthenticatedUserException() {
			$this->getJson( route( 'test.me' ) )->assertUnauthorized()->assertJson( [
				'message' => 'Unauthenticated.'
			] );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function setAuthenticatedUserSession() {
			$instance = $this->sphinx->session( $this->session );
			$this->assertInstanceOf( SphinxContract::class, $instance );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function setSessionFromRequest() {
			request()->headers->set( 'Authorization', 'Bearer ' . $this->token );

			$instance = $this->sphinx->session();
			$this->assertInstanceOf( SphinxContract::class, $instance );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function extractingToken() {
			$output = $this->sphinx->session( $this->session )->extract( $this->token );
			$this->assertInstanceOf( UnencryptedToken::class, $output );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function settingUpJwtProviders() {
			$instance = $this->sphinx->session( $this->session )->create( $this->user );
			$this->assertInstanceOf( SphinxContract::class, $instance );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function settingAClaim() {
			$token = $this->sphinx->session( $this->session )
			                      ->create( $this->user )
			                      ->claim( 'test', 'value' )
			                      ->accessToken();

			$unToken = $this->sphinx->session( $this->session )->getInsideToken( $token );

			$this->assertTrue( $unToken->claims()->has( 'test' ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function settingAHeader() {
			$token = $this->sphinx->session( $this->session )
			                      ->create( $this->user )
			                      ->header( 'test', 'value' )
			                      ->accessToken();

			$unToken = $this->sphinx->session( $this->session )->getInsideToken( $token );

			$this->assertTrue( $unToken->headers()->has( 'test' ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function gettingAnAccessToken() {
			$token = $this->sphinx->session( $this->session )
			                      ->create( $this->user )
			                      ->header( 'test', 'value' )
			                      ->claim( 'test', 'value' )
			                      ->accessToken();

			$this->assertIsString( $token );

			$unToken = $this->sphinx->session( $this->session )->getInsideToken( $token );

			$this->assertTrue( $unToken->headers()->has( 'test' ) );
			$this->assertTrue( $unToken->claims()->has( 'test' ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function validatingToken() {
			$this->assertTrue( $this->sphinx->session( $this->session )->validate( $this->token ) );
			$this->assertTrue( $this->sphinx->session( $this->session )->validateInsideToken( $this->token ) );

			$token = UserFactory::createAccessToken( UserFactory::createNormalUserWithSession() );
			$this->expectException( SphinxException::class );
			$this->expectErrorMessage( 'Token signature mismatch!' );

			$this->sphinx->session( $this->session )->assertInsideToken( $token );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function createARefreshToken() {
			$this->assertIsString( $refresh = $this->sphinx->session( $this->session )
			                                               ->createRefreshToken( $this->user )
			                                               ->refreshToken() );
			$unRefresh = $this->sphinx->session( $this->session )->extract( $refresh );

			$this->assertTrue( $unRefresh->headers()->has( 'refresh' ) );
			$this->assertTrue( (bool) $unRefresh->headers()->get( 'refresh' ) );

			$this->assertTrue( $unRefresh->headers()->has( 'session_id' ) );
			$this->assertEquals( $this->session->id, $unRefresh->headers()->get( 'session_id' ) );

			$insideRefresh = $this->sphinx->session( $this->session )->getInsideToken( $refresh );
			$this->assertTrue( $insideRefresh->claims()->has( 'user' ) );
			$this->assertEquals( $this->user->id, $insideRefresh->claims()->get( 'user' )[ 'id' ] );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function getUserPermissionsFromToken() {
			$this->assertInstanceOf( UnencryptedToken::class,
				$inside = $this->sphinx->session( $this->session )->getInsideToken( $this->token ) );
			$this->assertTrue( $inside->claims()->has( 'permissions' ) );
			$this->assertEquals( $this->user->getAllPermissions()->pluck( 'name', 'id' )->toArray(),
				$this->sphinx->session( $this->session )->getPermissions( $this->token ) );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function getInsideToken() {
			$this->assertInstanceOf( UnencryptedToken::class,
				$inside = $this->sphinx->session( $this->session )->getInsideToken( $this->token ) );

			$this->assertTrue( $inside->claims()->has( 'role' ) );
			$this->assertEquals( $this->user->roles()->first()->only( 'id', 'name' ),
				$inside->claims()->get( 'role' ) );

			$this->assertTrue( $inside->claims()->has( 'permissions' ) );
			$this->assertEquals( $this->user->getAllPermissions()->pluck( 'name', 'id' )->toArray(),
				$inside->claims()->get( 'permissions' ) );

			$this->assertTrue( $inside->claims()->has( 'user' ) );
			$this->assertEquals( array_merge( $this->user->extract(), [
				'id'                       => $this->user->id,
				$this->user->getUsername() => $this->user->{$this->user->getUsername()}
			] ), $inside->claims()->get( 'user' ) );
		}

	}
