<?php

namespace Hans\Sphinx\Tests\Instances;

    use Hans\Sphinx\Drivers\Contracts\JwtToken;
    use Lcobucci\JWT\Validation\Constraint;

    class JwtTokenWithCustomizableConstraintsInstance extends JwtToken
    {
        private array $constraints = [];

        /**
         * @return array
         */
        protected function getAvailableConstrains(): array
        {
            return $this->constraints;
        }

        public function registerConstrain(Constraint $constraint): self
        {
            $this->constraints[] = $constraint;

            $this->configuration->setValidationConstraints(...$this->getAvailableConstrains());
            $this->instance = $this->configuration->builder();

            return $this;
        }
    }
