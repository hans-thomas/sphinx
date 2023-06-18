<?php

	namespace Hans\Sphinx\Drivers\Constraints;

	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Lcobucci\JWT\Signer;
	use Lcobucci\JWT\Token;
	use Lcobucci\JWT\Validation\Constraint;
	use Symfony\Component\HttpFoundation\Response;

	final class SecretVerificationValidator implements Constraint {
		private Signer $signer;
		private Signer\Key $key;

		public function __construct( Signer $signer, Signer\Key $key ) {
			$this->signer = $signer;
			$this->key    = $key;
		}

		/**
		 * @param Token $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assert( Token $token ): void {
			if ( $token->headers()->get( 'alg' ) !== $this->signer->algorithmId() ) {
				throw new SphinxException(
					'Token signer mismatch!',
					SphinxErrorCode::TOKEN_MISMATCH,
					Response::HTTP_FORBIDDEN
				);
			}

			if ( ! $this->signer->verify( $token->signature()->hash(), $token->payload(), $this->key ) ) {
				throw new SphinxException(
					'Token signature mismatch!',
					SphinxErrorCode::TOKEN_MISMATCH,
					Response::HTTP_FORBIDDEN
				);
			}
		}
	}
