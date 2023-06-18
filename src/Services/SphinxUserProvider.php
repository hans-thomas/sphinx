<?php

	namespace Hans\Sphinx\Services;

	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Contracts\Auth\UserProvider;
	use Illuminate\Database\Eloquent\Model;

	class SphinxUserProvider implements UserProvider {

		public function __construct(
			private readonly array $config
		) {
		}

		/**
		 * Retrieve a user by their unique identifier and "remember me" token.
		 *
		 * @param mixed  $identifier
		 * @param string $token
		 *
		 * @return Authenticatable|null
		 */
		public function retrieveByToken( $identifier, $token ): ?Authenticatable {
			return $this->retrieveById( $identifier );
		}

		/**
		 * Retrieve a user by their unique identifier.
		 *
		 * @param mixed $identifier
		 *
		 * @return Authenticatable|null
		 */
		public function retrieveById( $identifier ): ?Authenticatable {
			return $this->makeModel()->query()->find( $identifier );
		}

		/**
		 * Update the "remember me" token for the given user in storage.
		 *
		 * @param Authenticatable $user
		 * @param string          $token
		 *
		 * @return void
		 */
		public function updateRememberToken( Authenticatable $user, $token ): void {
			// no action needed
		}

		/**
		 * Retrieve a user by the given credentials.
		 *
		 * @param array $credentials
		 *
		 * @return Authenticatable|null
		 */
		public function retrieveByCredentials( array $credentials ): ?Authenticatable {
			$model = $this->makeModel();
			if ( isset( $credentials[ 'password' ] ) ) {
				unset( $credentials[ 'password' ] );
			}

			if ( ! isset( $credentials[ 'id' ] ) and count( $credentials ) > 0 ) {
				$instance = $model->query()->firstWhere( $credentials );
			} else {
				/** @var Model $instance */
				$instance = $this->makeModel();
				$instance->fill( $credentials );
				$instance->id     = $credentials[ 'id' ];
				$instance->exists = true;
			}

			return $instance;
		}

		/**
		 * Validate a user against the given credentials.
		 *
		 * @param Authenticatable $user
		 * @param array           $credentials
		 *
		 * @return bool
		 */
		public function validateCredentials( Authenticatable $user, array $credentials ): bool {
			return count( array_diff( $credentials, $user->toArray() ) ) == 0;
		}

		/**
		 * Make an instance of given authenticatable class
		 *
		 * @return Authenticatable
		 */
		private function makeModel(): Authenticatable {
			return app( $this->config[ 'model' ] );
		}

	}
