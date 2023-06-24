<?php

	namespace Hans\Sphinx\Traits;

	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Hans\Sphinx\Models\Session;
	use Illuminate\Support\Facades\Cache;
	use Throwable;

	trait SphinxMethods {

		/**
		 * Hooks for increasing version on updates
		 *
		 * @return void
		 */
		protected static function hooks(): void {
			static::saved( function( self $model ) {
				$model->increaseVersion();
			} );
		}

		/**
		 * Increase the version and update related sessions
		 *
		 * @return bool
		 */
		public function increaseVersion(): bool {
			try {
				$sessionIds = $this->sessions()->select( 'id' )->get()->pluck( 'id' );
				Session::query()
				       ->whereIn( 'id', $sessionIds )
				       ->increment( 'sessionable_version' );

				$sessions = Session::query()->findMany( $sessionIds );
				foreach ( $sessions as $session ) {
					Cache::forget( $key = SphinxCache::SESSION . $session->id );
					Cache::forever( $key, $session );
				}
			} catch ( Throwable $e ) {
				return false;
			}

			return true;
		}

		/**
		 * Return user version using latest opened session
		 *
		 * @return int
		 */
		public function getVersion(): int {
			return $this->sessions()
			            ->latest()
			            ->select( 'id', 'sessionable_version' )
			            ->first()->sessionable_version ?? 1;
		}

		/**
		 * Determine the limitation of logged-in devices using one user credentials
		 *
		 * @return int
		 */
		abstract public function getDeviceLimit(): int;

		/**
		 * Extract necessary attributes of user
		 *
		 * @return array
		 */
		abstract public function extract(): array;

		/**
		 * Return username column name
		 *
		 * @return string
		 */
		abstract public function username(): string;

		/**
		 * Extract attributes of related role
		 *
		 * @return array|null
		 */
		abstract public function extractRole(): ?array;

		/**
		 * Extract related permissions attributes
		 *
		 * @return array|null
		 */
		abstract public function extractPermissions(): ?array;

	}