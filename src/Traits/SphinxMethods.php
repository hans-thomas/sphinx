<?php

	namespace Hans\Sphinx\Traits;

	use Illuminate\Support\Facades\Cache;
	use SphinxCacheEnum;
	use Throwable;

	trait SphinxMethods {

		protected static function booted() {
			static::saved( function( self $model ) {
				$model->increaseVersion();
				$user_version = $model->getVersion();
				collect( $model->sessions )->each( function( $session ) use ( $user_version ) {
					Cache::forget( $key = SphinxCacheEnum::SESSION . $session->id );
					Cache::forever( $key, array_merge( $session->toArray(), compact( 'user_version' ) ) );
				} );
			} );
		}

		public function increaseVersion(): bool {
			try {
				$this->forceFill( [ 'version' => $this->getVersion() + 1 ] );
				$this->saveQuietly();
			} catch ( Throwable $e ) {
				return false;
			}

			return true;
		}

		public function getVersion(): int {
			// TODO: if version was null, this->query()->where(...)->limit(1)->first()->version
			return $this->version ? : $this->fresh()->version;
		}

		abstract public function getDeviceLimit(): int;

		abstract public function extract(): array;

		abstract public static function username(): string;

		public function getUsername(): string {
			return static::username();
		}

	}