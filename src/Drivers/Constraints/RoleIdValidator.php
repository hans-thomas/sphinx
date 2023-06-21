<?php

	namespace Hans\Sphinx\Drivers\Constraints;

	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Lcobucci\JWT\Token;
	use Lcobucci\JWT\Validation\Constraint;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;

	final class RoleIdValidator implements Constraint {

		/**
		 * @param Token $token
		 *
		 * @throws SphinxException
		 */
		public function assert( Token $token ): void {
			$role_id      = $token->headers()->get( 'role_id', false );
			$role_version = $token->headers()->get( 'role_version', false );

			if ( ! $role_id ) {
				throw new SphinxException(
					'Role id not found in header!',
					SphinxErrorCode::ROLE_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}
			if ( ! $role_version ) {
				throw new SphinxException(
					'Role\'s version not found in header!',
					SphinxErrorCode::ROLE_VERSION_NOT_FOUND,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}

			$role = app( sphinx_config( 'role_model' ) )->findAndCache( $role_id );

			if ( $role?->getVersion() != $role_version ) {
				throw new SphinxException(
					'User\'s token is out-of-date!',
					SphinxErrorCode::TOKEN_IS_OUT_OF_DATE,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}
		}
	}
