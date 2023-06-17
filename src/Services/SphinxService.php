<?php


	namespace Hans\Sphinx\Services;


	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Providers\InnerTokenProvider;
	use Hans\Sphinx\Providers\WrapperTokenProvider;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Support\Facades\Cache;
	use Lcobucci\JWT\UnencryptedToken;
	use SphinxCacheEnum;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;
	use Throwable;

	class SphinxService {

		/**
		 * @var WrapperTokenProvider
		 */
		private WrapperTokenProvider $wrapperProvider;

		/**
		 * @var InnerTokenProvider
		 */
		private InnerTokenProvider $innerProvider;

		/**
		 * @var object|null
		 */
		private object|null $session = null;

		/**
		 * @throws SphinxException
		 */
		public function __construct() {
			$this->wrapperProvider = new WrapperTokenProvider( sphinx_config( 'private_key' ) );
			$this->guessSession();
		}

		/**
		 * @return void
		 * @throws SphinxException
		 */
		private function guessSession(): void {
			if ( $token = request()->bearerToken() ) {
				$session_id = $this->wrapperProvider->decode( $token )->headers()->get( 'session_id', null );

				try {
					$cachedSession = Cache::rememberForever(
						SphinxCacheEnum::SESSION . $session_id,
						// TODO: findAndCache method for Session model
						fn() => Session::query()->findOrFail( $session_id )?->getForCache()
					);
					$this->session = (object) $cachedSession;
				} catch ( Throwable $e ) {
					throw new SphinxException(
						'Token expired! probably reached your device count limit.',
						SphinxErrorCode::TOKEN_EXPIRED,
						ResponseAlias::HTTP_FORBIDDEN
					);
				}

				$this->innerProvider = new InnerTokenProvider( $this->session->secret );
			}

		}

		/**
		 * @param Session $session
		 *
		 * @return $this
		 */
		public function session( Session $session ): self {
			$this->session = $session;

			return $this;
		}

		/**
		 * @param string $token
		 *
		 * @return UnencryptedToken
		 * @throws SphinxException
		 */
		public function extract( string $token ): UnencryptedToken {
			return $this->wrapperProvider->decode( $token );
		}

		/**
		 * @param Authenticatable $user
		 *
		 * @return $this
		 * @throws SphinxException
		 */
		public function create( Authenticatable $user ): self {
			try {
				$this->wrapperProvider->encode()
				                      ->expiresAt( sphinx_config( 'expired_at' ) )
				                      ->header( 'session_id', $this->session->id )
				                      ->header( 'user_version', $user->getVersion() )
					// TODO: determine inside of related model
					                  ->header( 'role_id', ( $role = $user->roles()->first() )->id )
				                      ->header( 'role_version', $role->getVersion() );

				$this->insideProvider->encode()
					// TODO: extractRoleData on model
					                 ->claim( 'role', $role->only( 'id', 'name' ) )
					// TODO: extractPermissionsData on model
					                 ->claim( 'permissions', $user->getAllPermissions()
					                                              ->pluck( 'name', 'id' )
					                                              ->toArray() )
				                     ->claim(
					                     'user',
					                     array_merge(
						                     $user->extract(),
						                     [
							                     'id'                 => $user->id,
							                     $user->getUsername() => $user->{$user->getUsername()}
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

			return $this;
		}

		/**
		 * @param string           $key
		 * @param string|int|array $value
		 *
		 * @return $this
		 */
		public function claim( string $key, string|int|array $value ): self {
			$this->innerProvider->claim( $key, $value );

			return $this;
		}

		/**
		 * @param string           $key
		 * @param string|int|array $value
		 *
		 * @return $this
		 */
		public function header( string $key, string|int|array $value ): self {
			$this->innerProvider->header( $key, $value );

			return $this;
		}

		/**
		 * @return string
		 */
		public function accessToken(): string {
			$this->wrapperProvider->claim( '_token', $this->innerProvider->getToken()->toString() );

			return $this->wrapperProvider->getToken()->toString();
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function validate( string $token ): bool {
			return $this->wrapperProvider->validate( $token );
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assert( string $token ): void {
			$this->wrapperProvider->assert( $token );
		}

		/**
		 * @param Authenticatable $user
		 *
		 * @return $this
		 * @throws SphinxException
		 */
		public function createRefreshToken( Authenticatable $user ): self {
			try {
				$this->wrapperProvider
					->encode()
					->expiresAt( sphinx_config( 'refresh_expired_at' ) )
					->header( 'refresh', true )
					->header( 'session_id', $this->session->id );
				$this->innerProvider
					->encode()
					->claim(
						'user',
						array_merge(
							$user->extract(),
							[
								'id'                 => $user->id,
								$user->getUsername() => $user->{$user->getUsername()}
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

			return $this;
		}

		/**
		 * @return string
		 */
		public function refreshToken(): string {
			$this->wrapperProvider->claim( '_token', $this->innerProvider->getToken()->toString() );

			return $this->wrapperProvider->getToken()->toString();
		}

		/**
		 * @param string $token
		 *
		 * @return array
		 * @throws SphinxException
		 */
		public function getPermissions( string $token ): array {
			return $this->getInsideToken( $token )->claims()->get( 'permissions' );
		}

		/**
		 * @param string $token
		 *
		 * @return UnencryptedToken
		 * @throws SphinxException
		 */
		public function getInsideToken( string $token ): UnencryptedToken {
			$this->assertInsideToken( $token );
			$token       = $this->wrapperProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerProvider->decode( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assertInsideToken( string $token ): void {
			$token       = $this->wrapperProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			$this->innerProvider->assert( $insideToken );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function validateInsideToken( string $token ): bool {
			$token       = $this->wrapperProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->innerProvider->validate( $insideToken );
		}
	}
