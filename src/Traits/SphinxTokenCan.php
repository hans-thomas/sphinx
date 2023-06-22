<?php

	namespace Hans\Sphinx\Traits;

	use Hans\Sphinx\Facades\Sphinx;
	use Illuminate\Support\Str;

	trait SphinxTokenCan {

		/**
		 * @var array
		 */
		private array $tokenPermissions;

		/**
		 * Determine if the entity has the given abilities.
		 *
		 * @param       $abilities
		 * @param array $arguments
		 *
		 * @return bool
		 */
		public function can( $abilities, $arguments = [] ): bool {
			if ( is_string( $abilities ) or is_int( $abilities ) ) {
				return $this->tokenCan( $abilities );
			}

			if ( is_array( $abilities ) ) {
				foreach ( $abilities as $ability ) {
					if ( ! $this->can( $ability ) ) {
						return false;
					}
				}

				return true;
			}

			return false;
		}

		/**
		 * Determine if the entity has any of the given abilities.
		 *
		 * @param iterable|string $abilities
		 * @param array|mixed     $arguments
		 *
		 * @return bool
		 */
		public function canAny( $abilities, $arguments = [] ): bool {
			if ( is_string( $abilities ) or is_int( $abilities ) ) {
				return $this->can( $abilities );
			}

			if ( is_array( $abilities ) ) {
				foreach ( $abilities as $ability ) {
					if ( $this->can( $ability ) ) {
						return true;
					}
				}

				return false;
			}

			return false;
		}

		/**
		 * Alias for cant method
		 *
		 * @param iterable|string $abilities
		 * @param array|mixed     $arguments
		 *
		 * @return bool
		 */
		public function cannot( $abilities, $arguments = [] ): bool {
			return $this->cant( $abilities, $arguments );
		}

		/**
		 * Determine if the entity does not have the given abilities.
		 *
		 * @param iterable|string $abilities
		 * @param array|mixed     $arguments
		 *
		 * @return bool
		 */
		public function cant( $abilities, $arguments = [] ): bool {
			return ! $this->can( $abilities, $arguments );
		}

		/**
		 * @param string|int $ability
		 *
		 * @return bool
		 */
		private function tokenCan( string|int $ability ): bool {
			if ( ! isset( $this->tokenPermissions ) ) {
				$this->tokenPermissions = Sphinx::getPermissions( request()->bearerToken() );
			}

			$model = Str::beforeLast( $ability, '-' );

			// check for super permissions
			if ( in_array( "*-*", $this->tokenPermissions ) ) {
				return true;
			}
			if ( in_array( "$model-*", $this->tokenPermissions ) ) {
				return true;
			}

			// check for ability
			if ( is_int( $ability ) ) {
				return in_array( $ability, array_keys( $this->tokenPermissions ) );
			}
			if ( is_string( $ability ) ) {
				return in_array( $ability, array_values( $this->tokenPermissions ) );
			}

			return false;
		}

	}
