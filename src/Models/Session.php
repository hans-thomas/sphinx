<?php

	namespace Hans\Sphinx\Models;

	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Support\Facades\Cache;

	class Session extends Model {

		protected $fillable = [
			'ip',
			'device',
			'browser',
			'os',
			'secret'
		];

		protected static function booted() {
			self::saved( function( self $model ) {
				Cache::forget( SphinxCache::SESSION . $model->id );
				Cache::forever( SphinxCache::SESSION . $model->id, $model->getForCache() );
			} );
			self::deleted( function( self $model ) {
				Cache::forget( SphinxCache::SESSION . $model->id );
			} );
		}

		public function getForCache(): array {
			return array_merge(
				$this->only( 'id', 'ip', 'device', 'platform', 'secret' ),
				[ 'user_version' => $this->sessionable->getVersion() ]
			);
		}

		public function sessionable(): MorphTo {
			return $this->morphTo();
		}

	}
