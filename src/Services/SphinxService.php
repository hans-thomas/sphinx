<?php


	namespace Hans\Sphinx\Services;


	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Providers\InnerTokenProvider;
	use Hans\Sphinx\Providers\WrapperTokenProvider;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Support\Facades\Cache;
	use Lcobucci\JWT\UnencryptedToken;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;
	use Throwable;

	class SphinxService {

		/**
		 * @var WrapperTokenProvider
		 */
		private WrapperTokenProvider $wrapperAccessTokenProvider;

		/**
		 * @var InnerTokenProvider
		 */
		private InnerTokenProvider $innerAccessTokenProvider;

		/**
		 * @var WrapperTokenProvider
		 */
		private WrapperTokenProvider $wrapperRefreshTokenProvider;

		/**
		 * @var InnerTokenProvider
		 */
		private InnerTokenProvider $innerRefreshTokenProvider;

		/**
		 * @var object|null
		 */
		private ?object $session = null;

		/**
		 * @throws SphinxException
		 */
		public function __construct() {
			$this->wrapperAccessTokenProvider  = new WrapperTokenProvider( sphinx_config( 'private_key' ) );
			$this->wrapperRefreshTokenProvider = new WrapperTokenProvider( sphinx_config( 'private_key' ) );
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
		 * @throws SphinxException
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
		 * @return void
		 * @throws SphinxException
		 */
		public function assertInnerAccessToken( string $token ): void {
			$token       = $this->wrapperAccessTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			$this->innerAccessTokenProvider->assert( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function validateInnerAccessToken( string $token ): bool {
			$token       = $this->wrapperAccessTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerAccessTokenProvider->validate( $insideToken );
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
		 * @throws SphinxException
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
		 * @return bool
		 * @throws SphinxException
		 */
		public function validateInnerRefreshToken( string $token ): bool {
			$token       = $this->wrapperRefreshTokenProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerRefreshTokenProvider->validate( $insideToken );
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
				$this->wrapperAccessTokenProvider->encode()
				                                 ->expiresAt( sphinx_config( 'expired_at' ) )
				                                 ->header( 'session_id', $this->session->id )
				                                 ->header( 'user_version', $user->getVersion() )
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

				$this->innerAccessTokenProvider->encode()
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
					->header( 'refresh', true )
					->header( 'session_id', $this->session->id );
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
					$cachedSession = Cache::rememberForever(
						SphinxCache::SESSION . $session_id,
						// TODO: findAndCache method for Session model
						fn() => Session::query()->findOrFail( $session_id )->getForCache()
					);
					$this->session = (object) $cachedSession;
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
			$cachedSession   = Cache::rememberForever(
				SphinxCache::SESSION . $capturedSession->id,
				fn() => $capturedSession->getForCache()
			);
			$this->session   = (object) $cachedSession;

			$this->initInnerTokensInstance();
		}

		/**
		 * @return void
		 */
		private function initInnerTokensInstance(): void {
			$this->innerAccessTokenProvider  = new InnerTokenProvider( $this->session->secret );
			$this->innerRefreshTokenProvider = new InnerTokenProvider( $this->session->secret );
		}

	}
