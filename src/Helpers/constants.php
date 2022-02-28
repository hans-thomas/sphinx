<?php

	if ( ! class_exists( Enum::class ) ) {
		abstract class Enum {
			public static function toArray() {
				$vars = collect();
				foreach ( ( new ReflectionClass( static::class ) )->getConstants() as $name => $value ):
					$vars->put( $name, $value );
				endforeach;

				return $vars->toArray();
			}
		}
	}

	class SphinxCacheEnum extends Enum {
		public const ROLE = 'role_cache_';
		public const SESSION = 'session_cache_';
	}
