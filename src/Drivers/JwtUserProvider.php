<?php

	namespace Hans\Sphinx\Drivers;

	use App\Models\User;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Contracts\Auth\UserProvider;

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
			$instance         = ( new User() )->forceFill( [ 'id' => $identifier ] );
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
			$instance         = ( new User )->forceFill( $credentials );
			$instance->exists = true;

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
			if ( $user->id == ( $credentials[ 'id' ] ?? null ) ) {
				return true;
			}

			return false;
		}
	}
