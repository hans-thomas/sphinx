<?php

	namespace Hans\Sphinx\Traits;

	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Hans\Sphinx\Models\Session;
	use Illuminate\Support\Facades\Cache;
	use Throwable;

	trait SphinxMethods {

		/**
		 * Perform any actions required after the model boots.
		 *
		 * @return void
		 */
		protected static function hooks(): void {
			static::saved( function( self $model ) {
				$model->increaseVersion();
			} );
		}

		/**
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
		 * @return int
		 */
		public function getVersion(): int {
			return $this->sessions()
			            ->latest()
			            ->select( 'id', 'sessionable_version' )
			            ->first()->sessionable_version ?? 1;
		}

		/**
		 * @return int
		 */
		abstract public function getDeviceLimit(): int;

		/**
		 * @return array
		 */
		abstract public function extract(): array;

		/**
		 * @return string
		 */
		abstract public function username(): string;

		/**
		 * @return array|null
		 */
		abstract public function extractRole(): ?array;

		/**
		 * @return array|null
		 */
		abstract public function extractPermissions(): ?array;

	}