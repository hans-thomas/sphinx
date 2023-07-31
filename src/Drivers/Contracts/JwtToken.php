<?php

namespace Hans\Sphinx\Drivers\Contracts;

    use Closure;
    use DateTimeImmutable;
    use Hans\Sphinx\Exceptions\SphinxErrorCode;
    use Hans\Sphinx\Exceptions\SphinxException;
    use Lcobucci\JWT\Builder;
    use Lcobucci\JWT\Configuration;
    use Lcobucci\JWT\Signer\Hmac\Sha512;
    use Lcobucci\JWT\Signer\Key\InMemory;
    use Lcobucci\JWT\Token\Plain;
    use Lcobucci\JWT\UnencryptedToken;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;
    use Throwable;

    abstract class JwtToken
    {
        /**
         * Configuration for JWT builder and parser.
         *
         * @var Configuration
         */
        protected Configuration $configuration;

        /**
         * JWT builder instance.
         *
         * @var Builder
         */
        protected Builder $instance;

        public function __construct(string $secret)
        {
            // TODO: RSA support?
            $this->configuration = Configuration::forSymmetricSigner(
                new Sha512(),
                InMemory::plainText($secret)
            );
            $this->configuration->setValidationConstraints(...$this->getAvailableConstrains());
            $this->instance = $this->configuration->builder();
        }

        /**
         * Return available constrains for current implementation.
         *
         * @return array
         */
        abstract protected function getAvailableConstrains(): array;

        /**
         * Determine the token is issued by who.
         *
         * @param string $issuedBy
         *
         * @return self
         */
        public function issuedBy(string $issuedBy): self
        {
            $this->instance->issuedBy($issuedBy);

            return $this;
        }

        /**
         * Determine the token is permitted for what.
         *
         * @param string $permittedFor
         *
         * @return self
         */
        public function permittedFor(string $permittedFor): self
        {
            $this->instance->permittedFor($permittedFor);

            return $this;
        }

        /**
         * Determine the token is identified by who.
         *
         * @param string $identifiedBy
         *
         * @return self
         */
        public function identifiedBy(string $identifiedBy): self
        {
            $this->instance->identifiedBy($identifiedBy);

            return $this;
        }

        /**
         * Determine the token can be used after a period.
         *
         * @param string $due
         *
         * @return self
         */
        public function canOnlyBeUsedAfter(string $due = '+1 minute'): self
        {
            $date = new DateTimeImmutable();
            $this->instance->canOnlyBeUsedAfter($date->modify($due));

            return $this;
        }

        /**
         * Determine the token expires after a period.
         *
         * @param string $due
         *
         * @return self
         */
        public function expiresAt(string $due = '+5 hour'): self
        {
            $date = new DateTimeImmutable();
            $this->instance->expiresAt($date->modify($due));

            return $this;
        }

        /**
         * Set many claims at once.
         *
         * @param array $claims
         *
         * @throws SphinxException
         *
         * @return self
         */
        public function claims(array $claims): self
        {
            foreach ($claims as $key => $value) {
                try {
                    $this->claim($key, $value);
                } catch (Throwable $e) {
                    throw new SphinxException(
                        "Failed to set [$key => $value] claim.",
                        SphinxErrorCode::FAILED_TO_SET_CLAIM
                    );
                }
            }

            return $this;
        }

        /**
         * Set a claim for token.
         *
         * @param string           $key
         * @param string|int|array $value
         *
         * @return self
         */
        public function claim(string $key, string|int|array $value): self
        {
            $this->instance->withClaim($key, $value);

            return $this;
        }

        /**
         * Set a claim if condition is true.
         *
         * @param bool                      $condition
         * @param string                    $key
         * @param string|int|array|callable $value
         *
         * @return self
         */
        public function claimWhen(bool $condition, string $key, string|int|array|callable $value): self
        {
            if ($condition) {
                if ($value instanceof Closure) {
                    $value = $value();
                }
                $this->instance->withClaim($key, $value);
            }

            return $this;
        }

        /**
         * Set many headers at once.
         *
         * @param array $headers
         *
         * @throws SphinxException
         *
         * @return self
         */
        public function headers(array $headers): self
        {
            foreach ($headers as $key => $value) {
                try {
                    $this->header($key, $value);
                } catch (Throwable $e) {
                    throw new SphinxException(
                        "Failed to set [$key => $value] header.",
                        SphinxErrorCode::FAILED_TO_SET_HEADER
                    );
                }
            }

            return $this;
        }

        /**
         * Set a header for token.
         *
         * @param string     $key
         * @param string|int $value
         *
         * @return self
         */
        public function header(string $key, string|int $value): self
        {
            $this->instance->withHeader($key, $value);

            return $this;
        }

        /**
         * Set a header if condition is true.
         *
         * @param bool                $condition
         * @param string              $key
         * @param string|int|callable $value
         *
         * @return self
         */
        public function headerWhen(bool $condition, string $key, string|int|callable $value): self
        {
            if ($condition) {
                if ($value instanceof Closure) {
                    $value = $value();
                }
                $this->instance->withHeader($key, $value);
            }

            return $this;
        }

        /**
         * Set immutable time to the token as issued time.
         *
         * @return self
         */
        public function encode(): self
        {
            $this->instance->issuedAt(new DateTimeImmutable());

            return $this;
        }

        /**
         * Decode the given token.
         *
         * @param string $token
         *
         * @throws SphinxException
         *
         * @return UnencryptedToken
         */
        public function decode(string $token): UnencryptedToken
        {
            try {
                $decoded = $this->configuration->parser()->parse($token);
            } catch (Throwable $e) {
                throw new SphinxException(
                    'Failed to decode the token. '.$e->getMessage(),
                    SphinxErrorCode::DECODE_FAILED,
                    ResponseAlias::HTTP_FORBIDDEN
                );
            }

            return $decoded;
        }

        /**
         * Assert the given token with available constraints.
         *
         * @param string $token
         *
         * @throws SphinxException
         *
         * @return void
         */
        public function assert(string $token): void
        {
            $constraints = $this->configuration->validationConstraints();
            $this->configuration->validator()->assert($this->decode($token), ...$constraints);
        }

        /**
         * Validation the given token with available constraints.
         *
         * @param string $token
         *
         * @return bool
         */
        public function validate(string $token): bool
        {
            $constraints = $this->configuration->validationConstraints();

            try {
                $this->configuration->validator()->validate($this->decode($token), ...$constraints);
            } catch (Throwable $e) {
                return false;
            }

            return true;
        }

        /**
         * Return the configured plain token instance.
         *
         * @return Plain
         */
        public function getToken(): Plain
        {
            return $this->instance->getToken($this->configuration->signer(), $this->configuration->signingKey());
        }
    }
