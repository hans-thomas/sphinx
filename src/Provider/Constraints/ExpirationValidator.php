<?php

	namespace Hans\Sphinx\Provider\Constraints;

	use DateTimeImmutable;
	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Lcobucci\JWT\Token;
	use Lcobucci\JWT\Validation\Constraint;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;

	final class ExpirationValidator implements Constraint {

		/**
		 * @throws SphinxException
		 */
		public function assert( Token $token ): void {
			$diff = ( new DateTimeImmutable( 'UTC' ) )->diff( $token->claims()->get( 'exp' ) );
			if ( '-' == $diff->format( '%R' ) ) {
				throw new SphinxException( 'Token expired!', SphinxErrorCode::TOKEN_EXPIRED,
					ResponseAlias::HTTP_FORBIDDEN );
			}
		}
	}
