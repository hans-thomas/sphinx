<?php

	namespace Hans\Sphinx\Traits;

	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Illuminate\Support\Facades\Cache;
	use Throwable;

	trait SphinxMethods {

		/**
		 * Perform any actions required after the model boots.
		 *
		 * @return void
		 */
		protected static function booted() {
			static::saved( function( self $model ) {
				$model->increaseVersion();
				$user_version = $model->getVersion();
				collect( $model->sessions )->each( function( $session ) use ( $user_version ) {
					Cache::forget( $key = SphinxCache::SESSION . $session->id );
					Cache::forever( $key, array_merge( $session->toArray(), compact( 'user_version' ) ) );
				} );
			} );
		}

		/**
		 * @return bool
		 */
		public function increaseVersion(): bool {
			try {
				$this->forceFill( [ 'version' => $this->getVersion() + 1 ] );
				$this->saveQuietly();
			} catch ( Throwable $e ) {
				return false;
			}

			return true;
		}

		/**
		 * @return int
		 */
		public function getVersion(): int {
			return $this->version ? : static::query()->find( $this->id, [ 'version' ] )->version;
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