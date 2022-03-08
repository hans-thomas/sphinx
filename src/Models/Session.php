<?php

	namespace Hans\Sphinx\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Support\Facades\Cache;
	use SphinxCacheEnum;

	class Session extends Model {
		use HasFactory;

		protected $fillable = [ 'ip', 'device', 'platform', 'secret' ];

		protected static function booted() {
			self::created( function( self $model ) {
				Cache::forever( SphinxCacheEnum::SESSION . $model->id, $model->getForCache() );
			} );
			self::updated( function( self $model ) {
				Cache::forget( SphinxCacheEnum::SESSION . $model->id );
				Cache::forever( SphinxCacheEnum::SESSION . $model->id, $model->getForCache() );
			} );
			self::deleted( function( self $model ) {
				Cache::forget( SphinxCacheEnum::SESSION . $model->id );
			} );
		}

		public function getForCache() {
			return array_merge( $this->only( 'id', 'ip', 'device', 'platform', 'secret' ),
				[ 'userVersion' => $this->user->getVersion() ] );
		}

		public function user(): BelongsTo {
			return $this->belongsTo( config( 'sphinx.model' ) );
		}
	}
