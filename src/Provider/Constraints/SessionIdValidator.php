<?php

	namespace Hans\Sphinx\Provider\Constraints;

	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Illuminate\Support\Facades\Cache;
	use Lcobucci\JWT\Token;
	use Lcobucci\JWT\Validation\Constraint;
	use SphinxCacheEnum;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;

	final class SessionIdValidator implements Constraint {

		/**
		 * @throws SphinxException
		 */
		public function assert( Token $token ): void {
			$session_id   = $token->headers()->get( 'session_id', false );
			$user_version = $token->headers()->get( 'user_version', false );

			if ( ! $session_id ) {
				throw new SphinxException( 'Session id not found in header!', SphinxErrorCode::SESSION_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN );
			}
			if ( ! $user_version ) {
				throw new SphinxException( 'User\'s version not found in header!',
					SphinxErrorCode::USERS_VERSION_NOT_FOUND, ResponseAlias::HTTP_FORBIDDEN );
			}
			$session = Cache::rememberForever( SphinxCacheEnum::SESSION . $session_id, function() use ( $session_id ) {
				return Session::find( $session_id )->getForCache();
			} );

			if ( $session[ 'userVersion' ] != $user_version ) {
				throw new SphinxException( 'User\'s token is out-of-date!', SphinxErrorCode::TOKEN_IS_OUT_OF_DATE,
					ResponseAlias::HTTP_FORBIDDEN );
			}
		}
	}
