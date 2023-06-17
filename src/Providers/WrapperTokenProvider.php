<?php

	namespace Hans\Sphinx\Providers;

	use Hans\Sphinx\Providers\Constraints\ExpirationValidator;
	use Hans\Sphinx\Providers\Constraints\RoleIdValidator;
	use Hans\Sphinx\Providers\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Providers\Constraints\SessionIdValidator;
	use Hans\Sphinx\Providers\Contracts\Provider;

	class WrapperTokenProvider extends Provider {

		/**
		 * @return array
		 */
		protected function getAvailableConstrains(): array {
			return [
				new SecretVerificationValidator( $this->configuration->signer(), $this->configuration->signingKey() ),
				new SessionIdValidator(),
				new RoleIdValidator(),
				new ExpirationValidator()
			];
		}
	}