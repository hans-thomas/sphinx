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
	 * @method static SphinxService claim( string $key, string|int|array $value )
	 * @method static SphinxService header( string $key, string|int|array $value )
	 * @method static bool validateWrapperAccessToken( string $token )
	 * @method static void assertWrapperAccessToken( string $token )
	 * @method static bool validateInnerAccessToken( string $token )
	 * @method static void assertInnerAccessToken( string $token )
	 * @method static UnencryptedToken getInnerAccessToken( string $token )
	 * @method static bool validateWrapperRefreshToken( string $token )
	 * @method static void assertWrapperRefreshToken( string $token )
	 * @method static bool validateInnerRefreshToken( string $token )
	 * @method static void assertInnerRefreshToken( string $token )
	 * @method static UnencryptedToken getInnerRefreshToken( string $token )
	 * @method static array getPermissions( string $token )
	 * @method static bool isRefreshToken( string $token )
	 * @method static bool isNotRefreshToken( string $token )
	 * @method static object|null getCurrentSession()
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