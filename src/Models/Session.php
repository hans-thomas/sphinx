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
	 * @property int    $sessionable_version
	 *
	 * Relationships:
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
			'secret',
			'sessionable_version'
		];

		/**
		 * Perform any actions required after the model boots.
		 *
		 * @return void
		 */
		protected static function booted(): void {
			self::saved( function( self $model ) {
				Cache::forget( SphinxCache::SESSION . $model->id );
				Cache::forever( SphinxCache::SESSION . $model->id, $model );
			} );
			self::deleted( fn( self $model ) => Cache::forget( SphinxCache::SESSION . $model->id ) );
		}

		/**
		 * Sessionable relationship
		 *
		 * @return MorphTo
		 */
		public function sessionable(): MorphTo {
			return $this->morphTo();
		}

		/**
		 * Find the given id and cache the result
		 *
		 * @param int $id
		 *
		 * @return Session
		 */
		public static function findAndCache( int $id ): self {
			return Cache::rememberForever(
				SphinxCache::SESSION . $id,
				fn() => self::query()->findOrFail( $id )
			);
		}

	}
