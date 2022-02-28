<?php

	namespace Hans\Sphinx\Contracts;

	use Hans\Sphinx\Models\Session;
	use Illuminate\Contracts\Auth\Authenticatable;
	use Lcobucci\JWT\UnencryptedToken;

	interface SphinxContract {
		public function session( Session $session = null ): self;

		public function extract( string $token ): UnencryptedToken;

		public function create( Authenticatable $user ): self;

		public function claim( string $key, string|int|array $value ): self;

		public function header( string $key, string|int|array $value ): self;

		public function accessToken(): string;

		public function validate( string $token ): bool;

		public function validateInsideToken( string $token ): bool;

		public function assert( string $token ): void;

		public function assertInsideToken( string $token ): void;

		public function createRefreshToken( Authenticatable $user ): self;

		public function refreshToken(): string;

		public function getPermissions( string $token ): array;

		public function getInsideToken( string $token ): UnencryptedToken;
	}
