<?php

	namespace Hans\Sphinx\Provider;

	use Hans\Sphinx\Provider\Constraints\ExpirationValidator;
	use Hans\Sphinx\Provider\Constraints\RoleIdValidator;
	use Hans\Sphinx\Provider\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Provider\Constraints\SessionIdValidator;
	use Hans\Sphinx\Provider\Contracts\Provider;

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