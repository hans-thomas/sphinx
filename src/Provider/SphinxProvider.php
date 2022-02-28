<?php


	namespace Hans\Sphinx\Provider;


	use DateTimeImmutable;
	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Provider\Constraints\ExpirationValidator;
	use Hans\Sphinx\Provider\Constraints\RoleIdValidator;
	use Hans\Sphinx\Provider\Constraints\SecretVerificationValidator;
	use Hans\Sphinx\Provider\Constraints\SessionIdValidator;
	use Illuminate\Support\Arr;
	use Lcobucci\JWT\Builder;
	use Lcobucci\JWT\Configuration;
	use Lcobucci\JWT\Signer\Hmac\Sha512;
	use Lcobucci\JWT\Signer\Key\InMemory;
	use Lcobucci\JWT\UnencryptedToken;
	use Symfony\Component\HttpFoundation\Response as ResponseAlias;
	use Throwable;

	class SphinxProvider {
		private Configuration $configuration;
		private Builder $instance;

		public function __construct( string $secret, bool $inside = false ) {
			/*$this->configuration = Configuration::forAsymmetricSigner( new \Lcobucci\JWT\Signer\Rsa\Sha512(),
				LocalFileReference::file( __DIR__ . '/secret.pem' ),
				InMemory::base64Encoded( $this->getConfig( 'public_key' ) ) );*/
			try {
				$this->configuration = Configuration::forSymmetricSigner( new Sha512(),
					InMemory::base64Encoded( $secret ) );
			} catch ( Throwable $e ) {
				throw new SphinxException( $e->getMessage(), SphinxErrorCode::DECODE_FAILED,
					ResponseAlias::HTTP_FORBIDDEN );
			}
			$params = collect( [
				new SecretVerificationValidator( $this->configuration->signer(), $this->configuration->signingKey() ),
				new SessionIdValidator(),
				new RoleIdValidator(),
				new ExpirationValidator()
			] );
			$params = $inside ? Arr::wrap( $params->first() ) : $params;
			$this->configuration->setValidationConstraints( ...$params );
			$this->instance = $this->configuration->builder();
		}

		public function encode(): self {
			$now            = new DateTimeImmutable();
			$this->instance = $this->instance->issuedAt( $now );

			return $this;
		}

		public function issuedBy( string $issuedBy ): self {
			$this->instance = $this->instance->issuedBy( $issuedBy );

			return $this;
		}

		public function permittedFor( string $permittedFor ): self {
			$this->instance = $this->instance->permittedFor( $permittedFor );

			return $this;
		}

		public function identifiedBy( string $identifiedBy ): self {
			$this->instance = $this->instance->identifiedBy( $identifiedBy );

			return $this;
		}

		public function canOnlyBeUsedAfter( string $due = '+1 minute' ): self {
			$date           = new DateTimeImmutable();
			$this->instance = $this->instance->canOnlyBeUsedAfter( $date->modify( $due ) );

			return $this;
		}

		public function expiresAt( string $due = '+5 hour' ): self {
			$date           = new DateTimeImmutable();
			$this->instance = $this->instance->expiresAt( $date->modify( $due ) );

			return $this;
		}

		public function claims( array $claims ): self {
			foreach ( $claims as $key => $value ) {
				try {
					$this->claim( $key, $value );
				} catch ( Throwable $e ) {
					\Log::error( 'Xjwt error -> claims',
						[ 'key: ' . $key . ' value: ' . $value . ' ain\'t allowed!' ] );
				}
			}

			return $this;
		}

		public function claim( string $key, string|int|array $value ): self {
			$this->instance->withClaim( $key, $value );

			return $this;
		}

		public function headers( array $headers ): self {
			foreach ( $headers as $key => $value ) {
				try {
					$this->header( $key, $value );
				} catch ( Throwable $e ) {
					\Log::error( 'Xjwt error -> headers',
						[ 'key: ' . $key . ' value: ' . $value . ' ain\'t allowed!' ] );
				}
			}

			return $this;
		}

		public function header( string $key, string|int $value ): self {
			$this->instance->withHeader( $key, $value );

			return $this;
		}

		public function getToken( bool $asObject = false ): \Lcobucci\JWT\Token\Plain|string {
			$token = $this->instance->getToken( $this->configuration->signer(), $this->configuration->signingKey() );

			return $asObject ? $token : $token->toString();
		}

		public function validate( string $token ): bool {
			$constraints = $this->configuration->validationConstraints();
			if ( ! $this->configuration->validator()->validate( $this->decode( $token ), ...$constraints ) ) {
				return false;
			}

			return true;
		}

		public function decode( string $string ): UnencryptedToken {
			try {
				$token = $this->configuration->parser()->parse( $string );
			} catch ( Throwable $e ) {
				throw new SphinxException( $e->getMessage(), SphinxErrorCode::DECODE_FAILED,
					ResponseAlias::HTTP_FORBIDDEN );
			}

			return $token;
		}

		public function assert( string $token ): void {
			$constraints = $this->configuration->validationConstraints();
			$this->configuration->validator()->assert( $this->decode( $token ), ...$constraints );
		}

		public function validateInside( string $token ): bool {
			$constraints = $this->configuration->validationConstraints();
			if ( ! $this->configuration->validator()->validate( $this->decode( $token ), ...$constraints ) ) {
				return false;
			}

			return true;
		}

		public function assertInside( string $token ): void {
			$constraints = $this->configuration->validationConstraints();
			$this->configuration->validator()->assert( $this->decode( $token ), ...$constraints );
		}
	}
