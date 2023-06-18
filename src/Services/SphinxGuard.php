<?php


	namespace Hans\Sphinx\Services;


	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Facades\Sphinx;
	use Illuminate\Auth\GuardHelpers;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Contracts\Auth\Guard;
	use Illuminate\Http\Request;
	use Illuminate\Support\Traits\Macroable;

	class SphinxGuard implements Authenticatable, Guard {
		use GuardHelpers, Macroable;


		public function __construct(
			protected $provider,
			private readonly Request $request,
		) {

			if ( $token = $request->bearerToken() and Sphinx::isNotRefreshToken( $token ) ) {
				Sphinx::assertWrapperAccessToken( $token );
				$this->user = $this->provider
					->retrieveByCredentials(
						$credentials = Sphinx::getInnerAccessToken( $token )
						                     ->claims()
						                     ->get( 'user' )
					);
				if (
					! $this->provider->validateCredentials( $this->user, $credentials )
				) {
					throw new SphinxException(
						"Failed to validate given credentials.",
						SphinxErrorCode::FAILED_TO_VALIDATE_CREDENTIALS
					);
				}

			}
		}

		/**
		 * Get the name of the unique identifier for the user.
		 *
		 * @return string
		 */
		public function getAuthIdentifierName(): string {
			return 'id';
		}

		/**
		 * Get the unique identifier for the user.
		 *
		 * @return mixed
		 */
		public function getAuthIdentifier(): mixed {
			return $this->user->{$this->getAuthIdentifierName()};
		}

		/**
		 * Get the password for the user.
		 *
		 * @return string
		 */
		public function getAuthPassword(): string {
			return $this->user->password;
		}

		/**
		 * Get the token value for the "remember me" session.
		 *
		 * @return void
		 */
		public function getRememberToken(): void {
			// no action needed
		}

		/**
		 * Set the token value for the "remember me" session.
		 *
		 * @param string $value
		 *
		 * @return void
		 */
		public function setRememberToken( $value ): void {
			// no action needed
		}

		/**
		 * Get the column name for the "remember me" token.
		 *
		 * @return void
		 */
		public function getRememberTokenName(): void {
			// no action needed
		}

		/**
		 * Get the currently authenticated user.
		 *
		 * @return Authenticatable|null
		 */
		public function user(): ?Authenticatable {
			return $this->user ?? null;
		}

		/**
		 * Attempt to authenticate a user using the given credentials.
		 *
		 * @param array $credentials
		 *
		 * @return bool
		 */
		public function attempt( array $credentials ): bool {
			$user = $this->provider->retrieveByCredentials( $credentials );
			if ( ! is_null( $user ) and $this->provider->validateCredentials( $user, $credentials ) ) {
				$this->login( $user );

				return true;
			}

			return false;
		}

		/**
		 * Log the given user ID into the application.
		 *
		 * @param int $id
		 *
		 * @return Authenticatable|null
		 */
		public function loginUsingId( int $id ): ?Authenticatable {
			$this->user = $this->provider->retrieveById( $id );

			return $this->user;
		}

		/**
		 * Validate a user's credentials.
		 *
		 * @param array $credentials
		 *
		 * @return bool
		 */
		public function validate( array $credentials = [] ): bool {
			if ( $this->provider->retrieveByCredentials( $credentials ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Log a user into the application.
		 *
		 * @param Authenticatable $user
		 *
		 * @return void
		 */
		public function login( Authenticatable $user ): void {
			$this->setUser( $user );
		}

	}
