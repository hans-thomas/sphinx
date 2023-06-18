<?php

	namespace Hans\Sphinx\Drivers\Constraints;

	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Hans\Sphinx\Models\Session;
	use Illuminate\Support\Facades\Cache;
	use Lcobucci\JWT\Token;
	use Lcobucci\JWT\Validation\Constraint;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;

	final class SessionIdValidator implements Constraint {

		/**
		 * @param Token $token
		 *
		 * @throws SphinxException
		 */
		public function assert( Token $token ): void {
			$session_id   = $token->headers()->get( 'session_id', false );
			$user_version = $token->headers()->get( 'user_version', false );

			if ( ! $session_id ) {
				throw new SphinxException(
					'Session id not found in header!',
					SphinxErrorCode::SESSION_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}
			if ( ! $user_version ) {
				throw new SphinxException(
					"User's version not found in header!",
					SphinxErrorCode::USERS_VERSION_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}

			$session = Cache::rememberForever(
				SphinxCache::SESSION . $session_id,
				fn() => Session::query()->findOrFail( $session_id )->getForCache()
			);

			if ( $session[ 'user_version' ] != $user_version ) {
				throw new SphinxException(
					"Token is out-of-date!",
					SphinxErrorCode::TOKEN_IS_OUT_OF_DATE,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}
		}
	}
