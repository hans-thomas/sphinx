<?php


	namespace Hans\Sphinx\Services;


	use Hans\Sphinx\Contracts\SphinxContract;
	use Illuminate\Auth\GuardHelpers;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Contracts\Auth\Guard;
	use Illuminate\Http\Request;

	class SphinxGuard implements Authenticatable, Guard {
		use GuardHelpers;

		private Request $request;
		private SphinxContract $sphinx;

		public function __construct( SphinxUserProvider $provider, Request $request, SphinxContract $sphinx_contract ) {
			$this->provider = $provider;
			$this->sphinx   = $sphinx_contract;
			$this->user     = null;
			$token          = $request->bearerToken();
			if ( $token ) {
				if ( ! $this->sphinx->extract( $token )->headers()->get( 'refresh', false ) ) {
					$this->sphinx->assert( $token );
					$this->user = $this->provider->retrieveByCredentials( $this->sphinx->getInsideToken( $token )
					                                                                   ->claims()
					                                                                   ->get( 'user' ) );
				}
			}
		}

		public function getAuthIdentifierName(): string {
			return 'id';
		}

		public function getAuthIdentifier() {
			return $this->user->{$this->getAuthIdentifierName()};
		}

		public function getAuthPassword(): string {
			return $this->user->password;
		}

		public function getRememberToken(): void {
			// no action needed
		}

		public function setRememberToken( $value ): void {
			// no action needed
		}

		public function getRememberTokenName(): void {
			// no action needed
		}

		public function user() {
			return $this->user;
		}

		public function attempt( array $credentials ): bool {
			$user = $this->provider->retrieveByCredentials( $credentials );
			if ( ! is_null( $user ) and $this->provider->validateCredentials( $user, $credentials ) ) {
				$this->login( $user );

				return true;
			}

			return false;
		}

		public function check(): bool {
			if ( isset( $this->user ) ) {
				return true;
			}

			return false;
		}

		public function loginUsingId( int $id ) {
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
		public function validate( array $credentials = [] ) {
			if ( $this->provider->retrieveByCredentials( $credentials ) ) {
				return true;
			}

			return false;
		}

		public function login( Authenticatable $user, bool $remember = false ): void {
			$this->setUser( $user );
		}


	}
