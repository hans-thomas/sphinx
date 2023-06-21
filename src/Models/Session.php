<?php

	namespace Hans\Sphinx\Models;

	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Support\Facades\Cache;

	/**
	 * Attributes:
	 *
	 * @property int    $id
	 * @property string $ip
	 * @property string $device
	 * @property string $browser
	 * @property string $os
	 * @property string $secret
	 *
	 *
	 * @property Model  $sessionable
	 *
	 */
	class Session extends Model {

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<string>
		 */
		protected $fillable = [
			'ip',
			'device',
			'browser',
			'os',
			'secret'
		];

		/**
		 * Perform any actions required after the model boots.
		 *
		 * @return void
		 */
		protected static function booted() {
			self::saved( function( self $model ) {
				Cache::forget( SphinxCache::SESSION . $model->id );
				Cache::forever( SphinxCache::SESSION . $model->id, $model->getForCache() );
			} );
			self::deleted( function( self $model ) {
				Cache::forget( SphinxCache::SESSION . $model->id );
			} );
		}

		/**
		 * @return MorphTo
		 */
		public function sessionable(): MorphTo {
			return $this->morphTo();
		}

		/**
		 * @param int $id
		 *
		 * @return object
		 */
		public static function findAndCache( int $id ): object {
			return Cache::rememberForever(
				SphinxCache::SESSION . $id,
				fn() => self::query()->findOrFail( $id )->getForCache()
			);
		}

		/**
		 * @return object
		 */
		public function getForCache(): object {
			return (object) array_merge(
				$this->toArray(),
				[ 'sessionable_version' => $this->sessionable->getVersion() ]
			);
		}

	}
