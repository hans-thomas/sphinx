<?php

	namespace Hans\Sphinx\Drivers;

	use Hans\Sphinx\Drivers\Constraints\ExpirationValidator;
	use Hans\Sphinx\Drivers\Constraints\RoleIdValidator;
	use Hans\Sphinx\Drivers\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Drivers\Constraints\SessionIdValidator;
	use Hans\Sphinx\Drivers\Contracts\JwtToken;

	class WrapperToken extends JwtToken {

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