<?php


	namespace Hans\Sphinx\Services;


	use Hans\Sphinx\Drivers\InnerAccessToken;
	use Hans\Sphinx\Drivers\InnerRefreshToken;
	use Hans\Sphinx\Drivers\WrapperAccessToken;
	use Hans\Sphinx\Drivers\WrapperRefreshToken;
	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Lcobucci\JWT\UnencryptedToken;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;
	use Throwable;

	class SphinxService {

		/**
		 * @var WrapperAccessToken
		 */
		private WrapperAccessToken $wrapperAccessTokenProvider;

		/**
		 * @var InnerAccessToken
		 */
		private InnerAccessToken $innerAccessTokenProvider;

		/**
		 * @var WrapperRefreshToken
		 */
		private WrapperRefreshToken $wrapperRefreshTokenProvider;

		/**
		 * @var InnerRefreshToken
		 */
		private InnerRefreshToken $innerRefreshTokenProvider;

		/**
		 * @var object|null
		 */
		private ?object $session = null;

		/**
		 * @throws SphinxException
		 */
		public function __construct() {
			$this->wrapperAccessTokenProvider  = new WrapperAccessToken( sphinx_config( 'secret' ) );
			$this->wrapperRefreshTokenProvider = new WrapperRefreshToken( sphinx_config( 'secret' ) );
			$this->guessSession();
		}

		/**
		 * @param string $token
		 *
		 * @return UnencryptedToken
		 * @throws SphinxException
		 */
		public function decode( string $token ): UnencryptedToken {
			return $this->wrapperAccessTokenProvider->decode( $token );
		}

		/**
		 * @throws SphinxException
		 */
		public function generateTokenFor( Authenticatable $user ): self {
			$this->openASessionFor( $user );

			$this->createAccessToken( $user );
			$this->createRefreshToken( $user );

			return $this;
		}

		/**
		 * @return string
		 */
		public function getAccessToken(): string {
			$this->wrapperAccessTokenProvider
				->claim( '_token', $this->innerAccessTokenProvider->getToken()->toString() );

			return $this->wrapperAccessTokenProvider->getToken()->toString();
		}

		/**
		 * @return string
		 */
		public function getRefreshToken(): string {
			$this->wrapperRefreshTokenProvider
				->claim( '_token', $this->innerRefreshTokenProvider->getToken()->toString() );

			return $this->wrapperRefreshTokenProvider->getToken()->toString();
		}

		/**
		 * @param string           $key
		 * @param string|int|array $value
		 *
		 * @return $this
		 */
		public function claim( string $key, string|int|array $value ): self {
			$this->innerAccessTokenProvider->claim( $key, $value );

			return $this;
		}

		/**
		 * @param string           $key
		 * @param string|int|array $value
		 *
		 * @return $this
		 */
		public function header( string $key, string|int|array $value ): self {
			$this->innerAccessTokenProvider->header( $key, $value );

			return $this;
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 */
		public function validateWrapperAccessToken( string $token ): bool {
			return $this->wrapperAccessTokenProvider->validate( $token );
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assertWrapperAccessToken( string $token ): void {
			$this->wrapperAccessTokenProvider->assert( $token );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function validateInnerAccessToken( string $token ): bool {
			if ( ! $this->validateWrapperAccessToken( $token ) ) {
				return false;
			}
			$token       = $this->wrapperAccessTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerAccessTokenProvider->validate( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assertInnerAccessToken( string $token ): void {
			$this->assertWrapperAccessToken( $token );
			$token       = $this->wrapperAccessTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			$this->innerAccessTokenProvider->assert( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return UnencryptedToken
		 * @throws SphinxException
		 */
		public function getInnerAccessToken( string $token ): UnencryptedToken {
			$this->assertInnerAccessToken( $token );
			$token       = $this->wrapperAccessTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerAccessTokenProvider->decode( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 */
		public function validateWrapperRefreshToken( string $token ): bool {
			return $this->wrapperRefreshTokenProvider->validate( $token );
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assertWrapperRefreshToken( string $token ): void {
			$this->wrapperRefreshTokenProvider->assert( $token );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function validateInnerRefreshToken( string $token ): bool {
			if ( ! $this->validateWrapperRefreshToken( $token ) ) {
				return false;
			}
			$token       = $this->wrapperRefreshTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerRefreshTokenProvider->validate( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assertInnerRefreshToken( string $token ): void {
			$token       = $this->wrapperRefreshTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			$this->innerRefreshTokenProvider->assert( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return UnencryptedToken
		 * @throws SphinxException
		 */
		public function getInnerRefreshToken( string $token ): UnencryptedToken {
			$this->assertInnerRefreshToken( $token );
			$token       = $this->wrapperRefreshTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerRefreshTokenProvider->decode( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return array
		 * @throws SphinxException
		 */
		public function getPermissions( string $token ): array {
			return $this->getInnerAccessToken( $token )->claims()->get( 'permissions' );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function isRefreshToken( string $token ): bool {
			return $this->decode( $token )->headers()->get( 'refresh', false );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function isNotRefreshToken( string $token ): bool {
			return ! $this->isRefreshToken( $token );
		}

		/**
		 * @param Authenticatable $user
		 *
		 * @return void
		 * @throws SphinxException
		 */
		private function createAccessToken( Authenticatable $user ): void {
			try {
				$this->wrapperAccessTokenProvider
					->encode()
					->expiresAt( sphinx_config( 'access_expired_at' ) )
					->header( 'session_id', $this->session->id )
					->header( 'sessionable_version', $user->getVersion() )
					->headerWhen(
						isset( $user->extractRole()[ 'id' ] ),
						'role_id',
						fn() => $user->extractRole()[ 'id' ]
					)
					->headerWhen(
						isset( $user->extractRole()[ 'version' ] ),
						'role_version',
						fn() => $user->extractRole()[ 'version' ]
					);

				$this->innerAccessTokenProvider
					->encode()
					->claim(
						'role',
						collect( $user->extractRole() )->only( 'id', 'name' )
					)
					->claim(
						'permissions',
						collect( $user->extractPermissions() )
							->pluck( 'name', 'id' )
							->toArray()
					)
					->claim(
						'user',
						array_merge(
							$user->extract(),
							[
								'id'              => $user->id,
								$user->username() => $user->{$user->username()}
							]
						)
					);
			} catch ( Throwable $e ) {
				throw new SphinxException(
					'Failed to create token! ' . $e->getMessage(),
					SphinxErrorCode::FAILED_TO_CREATE_TOKEN,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}

		}

		/**
		 * @param Authenticatable $user
		 *
		 * @return void
		 * @throws SphinxException
		 */
		private function createRefreshToken( Authenticatable $user ): void {
			try {
				$this->wrapperRefreshTokenProvider
					->encode()
					->expiresAt( sphinx_config( 'refresh_expired_at' ) )
					->header( 'refresh', true );
				$this->innerRefreshTokenProvider
					->encode()
					->claim(
						'user',
						array_merge(
							$user->extract(),
							[
								'id'              => $user->id,
								$user->username() => $user->{$user->username()}
							]
						)
					);
			} catch ( Throwable $e ) {
				throw new SphinxException(
					'Failed to create refresh token! ' . $e->getMessage(),
					SphinxErrorCode::FAILED_TO_CREATE_REFRESH_TOKEN,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}

		}

		/**
		 * @return void
		 * @throws SphinxException
		 */
		private function guessSession(): void {
			if ( $token = request()->bearerToken() ) {
				$session_id = $this->wrapperAccessTokenProvider
					->decode( $token )
					->headers()
					->get( 'session_id' );

				try {
					$this->session = Session::findAndCache( $session_id );
				} catch ( Throwable $e ) {
					throw new SphinxException(
						'Token expired! probably reached your device count limit.',
						SphinxErrorCode::TOKEN_EXPIRED,
						ResponseAlias::HTTP_FORBIDDEN
					);
				}

				$this->initInnerTokensInstance();
			}

		}

		/**
		 * @param Authenticatable $user
		 *
		 * @return void
		 * @throws SphinxException
		 */
		private function openASessionFor( Authenticatable $user ): void {
			$capturedSession = capture_session( $user );
			$this->session   = Session::findAndCache( $capturedSession->id );

			$this->initInnerTokensInstance();
		}

		/**
		 * @return void
		 */
		private function initInnerTokensInstance(): void {
			$this->innerAccessTokenProvider  = new InnerAccessToken( $this->session->secret );
			$this->innerRefreshTokenProvider = new InnerRefreshToken( $this->session->secret );
		}

		/**
		 * @return object|null
		 */
		public function getSession(): ?object {
			return $this->session;
		}

	}
