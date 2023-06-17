<?php

	namespace Hans\Sphinx\Providers;

	use Hans\Sphinx\Providers\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Providers\Contracts\Provider;

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