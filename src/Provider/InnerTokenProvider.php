<?php

	namespace Hans\Sphinx\Provider;

	use Hans\Sphinx\Provider\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Provider\Contracts\Provider;

	class InnerTokenProvider extends Provider {

		/**
		 * @return array
		 */
		protected function getAvailableConstrains(): array {
			return [
				new SecretVerificationValidator( $this->configuration->signer(), $this->configuration->signingKey() ),
			];
		}
	}