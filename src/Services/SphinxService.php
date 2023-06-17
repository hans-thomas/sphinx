<?php


	namespace Hans\Sphinx\Services;


	use Hans\Sphinx\Contracts\SphinxContract;
	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Providers\Contracts\Provider;
	use Hans\Sphinx\Providers\InnerTokenProvider;
	use Hans\Sphinx\Providers\WrapperTokenProvider;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Support\Arr;
	use Illuminate\Support\Facades\Cache;
	use Lcobucci\JWT\UnencryptedToken;
	use SphinxCacheEnum;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;

	class SphinxService implements SphinxContract {
		private Provider $mainProvider, $insideProvider;
		private array $configuration;
		private Provider $mainInstance, $insideInstance;
		private object|null $session = null;

		/**
		 * @throws SphinxException
		 */
		public function __construct() {
			$this->configuration = config( 'sphinx' );
			$this->mainProvider  = new WrapperTokenProvider( $this->getConfig( 'private_key' ) );
			$this->session();
		}

		private function getConfig( string $key ) {
			return Arr::get( $this->configuration, $key );
		}

		public function session( Session $session = null ): self {
			// TODO: make guessing ans setting session separate
			if ( $session ) {
				$this->session = $session;
			} else if ( $token = request()->bearerToken() ) {
				$session_id    = $this->mainProvider->decode( $token )->headers()->get( 'session_id', null );
				// TODO: findAndCache method for Session model
				$cachedSession = Cache::rememberForever( SphinxCacheEnum::SESSION . $session_id,
					function() use ( $session_id ) {
						return Session::find( $session_id )?->getForCache();
					} );
				if ( $cachedSession ) {
					$this->session = (object) $cachedSession;
				} else {
					throw new SphinxException( 'Token expired!', SphinxErrorCode::TOKEN_EXPIRED,
						ResponseAlias::HTTP_FORBIDDEN );
				}
			}
			if ( $secret = $this->session?->secret ) {
				$this->insideProvider = new InnerTokenProvider( $secret, true );
			}

			return $this;
		}

		public function setConfig( array $config ): self {
			// TODO: should be deleted
			if ( app()->runningUnitTests() ) {
				$this->configuration = $config;
			} else {

			}

			return $this;
		}

		public function extract( string $token ): UnencryptedToken {
			return $this->mainProvider->decode( $token );
		}

		public function create( Authenticatable $user ): self {
			try {
				$this->mainInstance = $this->mainProvider->encode()
				                                         ->expiresAt( $this->getConfig( 'expired_at' ) )
				                                         ->header( 'session_id', $this->session->id )
				                                         ->header( 'user_version', $user->getVersion() )
				                                         ->header( 'role_id', ( $role = $user->roles()->first() )->id )
				                                         ->header( 'role_version', $role->getVersion() );

				$this->insideInstance = $this->insideProvider->encode()
				                                             ->claim( 'role', $role->only( 'id', 'name' ) )
				                                             ->claim( 'permissions', $user->getAllPermissions()
				                                                                          ->pluck( 'name', 'id' )
				                                                                          ->toArray() )
				                                             ->claim( 'user', array_merge( $user->extract(), [
					                                             'id'                 => $user->id,
					                                             $user->getUsername() => $user->{$user->getUsername()}
				                                             ] ) );
			} catch ( \Throwable $e ) {
				throw new SphinxException( 'Creating the token failed!', SphinxErrorCode::SESSION_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN );
			}

			return $this;
		}

		public function claim( string $key, string|int|array $value ): self {
			$this->insideInstance = $this->insideInstance->claim( $key, $value );

			return $this;
		}

		public function header( string $key, string|int|array $value ): self {
			$this->insideInstance = $this->insideInstance->header( $key, $value );

			return $this;
		}

		public function accessToken(): string {
			$this->mainInstance->claim( '_token', $this->insideInstance->getToken() );

			return $this->mainInstance->getToken();
		}

		public function validate( string $token ): bool {
			return $this->mainProvider->validate( $token );
		}

		public function assert( string $token ): void {
			$this->mainProvider->assert( $token );
		}

		public function createRefreshToken( Authenticatable $user ): self {
			try {
				$this->mainInstance   = $this->mainProvider->encode()
				                                           ->expiresAt( $this->getConfig( 'refreshExpired_at' ) )
				                                           ->header( 'refresh', true )
				                                           ->header( 'session_id', $this->session->id );
				$this->insideInstance = $this->insideProvider->encode()->claim( 'user', array_merge( $user->extract(), [
					'id'                 => $user->id,
					$user->getUsername() => $user->{$user->getUsername()}
				] ) );
			} catch ( \Throwable $e ) {
				throw new SphinxException( 'Creating the refresh token failed!', SphinxErrorCode::SESSION_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN );
			}

			return $this;
		}

		public function refreshToken(): string {
			$this->mainInstance->claim( '_token', $this->insideInstance->getToken() );

			return $this->mainInstance->getToken();
		}

		public function getPermissions( string $token ): array {
			return $this->getInsideToken( $token )->claims()->get( 'permissions' );
		}

		public function getInsideToken( string $token ): UnencryptedToken {
			$this->assertInsideToken( $token );
			$token       = $this->mainProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->insideProvider->decode( $insideToken );
		}

		public function assertInsideToken( string $token ): void {
			$token       = $this->mainProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );
			$this->insideProvider->assert( $insideToken );
		}

		public function validateInsideToken( string $token ): bool {
			$token       = $this->mainProvider->decode( $token );
			$insideToken = $token->claims()->get( '_token' );

			return $this->insideProvider->validate( $insideToken );
		}
	}
