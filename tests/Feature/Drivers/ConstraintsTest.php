<?php

	namespace Hans\Sphinx\Tests\Feature\Drivers;

	use Hans\Sphinx\Drivers\Constraints\ExpirationValidator;
	use Hans\Sphinx\Drivers\Constraints\RoleIdValidator;
	use Hans\Sphinx\Drivers\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Drivers\Constraints\SessionIdValidator;
	use Hans\Sphinx\Drivers\Contracts\JwtToken;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\Instances\JwtTokenWithCustomizableConstraintsInstance;
	use Hans\Sphinx\Tests\TestCase;
	use Illuminate\Database\Eloquent\Model;
	use Lcobucci\JWT\Signer\Hmac\Sha512;
	use Lcobucci\JWT\Signer\Key\InMemory;

	class ConstraintsTest extends TestCase {

		private JwtToken $instance;
		private Model $user;
		private string $secret;

		protected function setUp(): void {
			parent::setUp();
			$this->instance = new JwtTokenWithCustomizableConstraintsInstance( $this->secret = generate_secret_key() );
			$this->user     = UserFactory::createNormalUser();
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function ExpirationValidator(): void {
			$this->instance->registerConstrain( new ExpirationValidator() );

			$token = $this->instance->expiresAt()->getToken()->toString();

			$this->instance->assert( $token );

			$token = $this->instance->expiresAt( '-1 second' )->getToken()->toString();

			$this->expectException( SphinxException::class );
			$this->expectExceptionMessage( 'Token expired!' );

			$this->instance->assert( $token );
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function RoleIdValidator(): void {
			$this->instance->registerConstrain( new RoleIdValidator() );

			$headers = [
				'role_id'      => $this->user->getRole()->id,
				'role_version' => $this->user->getRole()->getVersion(),
			];
			$token   = $this->instance->headers( $headers )->getToken()->toString();

			$this->instance->assert( $token );

			$this->user->getRole()->increaseVersion();

			$this->expectException( SphinxException::class );
			$this->expectExceptionMessage( "User's token is out-of-date!" );

			$this->instance->assert( $token );
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function SecretVerificationValidator(): void {
			$this->instance->registerConstrain(
				new SecretVerificationValidator( new Sha512(), InMemory::plainText( $this->secret ) )
			);

			$token = $this->instance->encode()->getToken()->toString();

			$this->instance->assert( $token );

			do {
				$randomAlpha = fake()->word()[ 0 ];
			} while ( $randomAlpha == $token[ strlen( $token ) - 1 ] );

			$token[ strlen( $token ) - 10 ] = $randomAlpha;

			$this->expectException( SphinxException::class );
			$this->expectExceptionMessage( 'Token signature mismatch!' );

			$this->instance->assert( $token );

		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function SessionIdValidator(): void {
			$this->instance->registerConstrain( new SessionIdValidator );

			$session = capture_session( $this->user );
			$headers = [
				'session_id'          => $session->id,
				'sessionable_version' => $session->sessionable->version,
			];

			$token = $this->instance->headers( $headers )->encode()->getToken()->toString();

			$this->instance->assert( $token );

			$session->sessionable->increaseVersion();

			$this->expectException( SphinxException::class );
			$this->expectExceptionMessage( 'Token is out-of-date!' );

			$this->instance->assert( $token );

		}

	}