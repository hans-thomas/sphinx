<?php

	namespace Hans\Sphinx\Facades;

	use Hans\Sphinx\Services\SphinxService;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Illuminate\Support\Facades\Facade;
	use Lcobucci\JWT\UnencryptedToken;
	use RuntimeException;

	/**
	 * @method static UnencryptedToken decode( string $token )
	 * @method static SphinxService generateTokenFor( Authenticatable $user )
	 * @method static string getAccessToken()
	 * @method static string getRefreshToken()
	 *
	 * @see SphinxService
	 */
	class Sphinx extends Facade {

		/**
		 * Get the registered name of the component.
		 *
		 * @return string
		 *
		 * @throws RuntimeException
		 */
		protected static function getFacadeAccessor() {
			return 'sphinx-service';
		}

	}