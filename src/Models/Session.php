<?php

	namespace Hans\Sphinx\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphTo;
	use Illuminate\Support\Facades\Cache;
	use SphinxCacheEnum;

	class Session extends Model {

		protected $fillable = [
			'ip',
			'device',
			'platform',
			'secret'
		];

		protected static function booted() {
			self::saved( function( self $model ) {
				Cache::forget( SphinxCacheEnum::SESSION . $model->id );
				Cache::forever( SphinxCacheEnum::SESSION . $model->id, $model->getForCache() );
			} );
			self::deleted( function( self $model ) {
				Cache::forget( SphinxCacheEnum::SESSION . $model->id );
			} );
		}

		public function getForCache(): array {
			return array_merge( $this->only( 'id', 'ip', 'device', 'platform', 'secret' ),
				[ 'user_version' => $this->sessionable->getVersion() ] );
		}

		public function sessionable(): MorphTo {
			return $this->morphTo();
		}

	}
