<?php


	namespace Hans\Sphinx\Providers\Contracts;


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

	abstract class Provider {

		/**
		 * @var Configuration
		 */
		protected Configuration $configuration;

		/**
		 * @var Builder
		 */
		protected Builder $instance;

		public function __construct( string $secret ) {
			$this->configuration = Configuration::forSymmetricSigner(
				new Sha512(),
				InMemory::plainText( $secret )
			);
			$this->configuration->setValidationConstraints( ...$this->getAvailableConstrains() );
			$this->instance = $this->configuration->builder();
		}

		/**
		 * @return array
		 */
		abstract protected function getAvailableConstrains(): array;

		/**
		 * @param string $issuedBy
		 *
		 * @return $this
		 */
		public function issuedBy( string $issuedBy ): self {
			$this->instance = $this->instance->issuedBy( $issuedBy );

			return $this;
		}

		/**
		 * @param string $permittedFor
		 *
		 * @return $this
		 */
		public function permittedFor( string $permittedFor ): self {
			$this->instance = $this->instance->permittedFor( $permittedFor );

			return $this;
		}

		/**
		 * @param string $identifiedBy
		 *
		 * @return $this
		 */
		public function identifiedBy( string $identifiedBy ): self {
			$this->instance = $this->instance->identifiedBy( $identifiedBy );

			return $this;
		}

		/**
		 * @param string $due
		 *
		 * @return $this
		 */
		public function canOnlyBeUsedAfter( string $due = '+1 minute' ): self {
			$date           = new DateTimeImmutable();
			$this->instance = $this->instance->canOnlyBeUsedAfter( $date->modify( $due ) );

			return $this;
		}

		/**
		 * @param string $due
		 *
		 * @return $this
		 */
		public function expiresAt( string $due = '+5 hour' ): self {
			$date           = new DateTimeImmutable();
			$this->instance = $this->instance->expiresAt( $date->modify( $due ) );

			return $this;
		}

		/**
		 * @param array $claims
		 *
		 * @return $this
		 * @throws SphinxException
		 */
		public function claims( array $claims ): self {
			foreach ( $claims as $key => $value ) {
				try {
					$this->claim( $key, $value );
				} catch ( Throwable $e ) {
					throw new SphinxException(
						"Failed to set [$key => $value] claim.",
						SphinxErrorCode::FAILED_TO_SET_CLAIM
					);
				}
			}

			return $this;
		}

		/**
		 * @param string           $key
		 * @param string|int|array $value
		 *
		 * @return $this
		 */
		public function claim( string $key, string|int|array $value ): self {
			$this->instance->withClaim( $key, $value );

			return $this;
		}

		/**
		 * @param array $headers
		 *
		 * @return $this
		 * @throws SphinxException
		 */
		public function headers( array $headers ): self {
			foreach ( $headers as $key => $value ) {
				try {
					$this->header( $key, $value );
				} catch ( Throwable $e ) {
					throw new SphinxException(
						"Failed to set [$key => $value] header.",
						SphinxErrorCode::FAILED_TO_SET_HEADER
					);
				}
			}

			return $this;
		}

		/**
		 * @param string     $key
		 * @param string|int $value
		 *
		 * @return $this
		 */
		public function header( string $key, string|int $value ): self {
			$this->instance->withHeader( $key, $value );

			return $this;
		}

		/**
		 * @return $this
		 */
		public function encode(): self {
			$now            = new DateTimeImmutable();
			$this->instance = $this->instance->issuedAt( $now );

			return $this;
		}

		/**
		 * @param string $string
		 *
		 * @return UnencryptedToken
		 * @throws SphinxException
		 */
		public function decode( string $string ): UnencryptedToken {
			try {
				$token = $this->configuration->parser()->parse( $string );
			} catch ( Throwable $e ) {
				throw new SphinxException(
					"Failed to decode the token. " . $e->getMessage(),
					SphinxErrorCode::DECODE_FAILED,
					ResponseAlias::HTTP_FORBIDDEN
				);
			}

			return $token;
		}

		/**
		 * @param string $token
		 *
		 * @return void
		 * @throws SphinxException
		 */
		public function assert( string $token ): void {
			$constraints = $this->configuration->validationConstraints();
			$this->configuration->validator()->assert( $this->decode( $token ), ...$constraints );
		}

		/**
		 * @param string $token
		 *
		 * @return bool
		 * @throws SphinxException
		 */
		public function validate( string $token ): bool {
			$constraints = $this->configuration->validationConstraints();
			if ( ! $this->configuration->validator()->validate( $this->decode( $token ), ...$constraints ) ) {
				return false;
			}

			return true;
		}

		/**
		 * @return Plain
		 */
		public function getToken(): Plain {
			return $this->instance->getToken( $this->configuration->signer(), $this->configuration->signingKey() );
		}

	}
