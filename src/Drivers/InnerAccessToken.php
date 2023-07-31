<?php

namespace Hans\Sphinx\Drivers;

    use Hans\Sphinx\Drivers\Constraints\SecretVerificationValidator;
    use Hans\Sphinx\Drivers\Contracts\JwtToken;

    class InnerAccessToken extends JwtToken
    {
        /**
         * Return available constrains for current implementation.
         *
         * @return array
         */
        protected function getAvailableConstrains(): array
        {
            return [
                new SecretVerificationValidator($this->configuration->signer(), $this->configuration->signingKey()),
            ];
        }
    }
