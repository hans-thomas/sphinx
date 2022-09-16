<?php

	namespace Hans\Sphinx\Drivers;

	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Contracts\Auth\UserProvider;
	use Illuminate\Support\Facades\Hash;

	class JwtUserProvider implements UserProvider {

		/**
		 * Retrieve a user by their unique identifier and "remember me" token.
		 *
		 * @param mixed  $identifier
		 * @param string $token
		 *
		 * @return Authenticatable|null
		 */
		public function retrieveByToken( $identifier, $token ) {
			return $this->retrieveById( $identifier );
		}

		/**
		 * Retrieve a user by their unique identifier.
		 *
		 * @param mixed $identifier
		 *
		 * @return Authenticatable|null
		 */
		public function retrieveById( $identifier ) {
			$instance         = ( new ( config( 'sphinx.model' ) ) )->forceFill( [ 'id' => $identifier ] );
			$instance->exists = true;

			return $instance;
		}

		/**
		 * Update the "remember me" token for the given user in storage.
		 *
		 * @param Authenticatable $user
		 * @param string          $token
		 *
		 * @return void
		 */
		public function updateRememberToken( Authenticatable $user, $token ) {
			// no action needed
		}

		/**
		 * Retrieve a user by the given credentials.
		 *
		 * @param array $credentials
		 *
		 * @return Authenticatable|null
		 */
		public function retrieveByCredentials( array $credentials ) {
			$model = new ( config( 'sphinx.model' ) )();
			if ( ! isset( $credentials[ 'id' ] ) and collect( $credentials )->except( [ 'password' ] )->count() > 0 ) {
				$instance = $model->query()->firstWhere( collect( $credentials )->except( [ 'password' ] )->toArray() );
			} else {
				$instance         = $model->forceFill( $credentials );
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
		public function validateCredentials( Authenticatable $user, array $credentials ) {
			return Hash::check( $credentials[ 'password' ], $user->getAuthPassword() );
		}
	}
