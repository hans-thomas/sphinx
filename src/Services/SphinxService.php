<?php

namespace Hans\Sphinx\Services;

use Hans\Sphinx\Drivers\InnerAccessToken;
use Hans\Sphinx\Drivers\InnerRefreshToken;
use Hans\Sphinx\Drivers\WrapperAccessToken;
use Hans\Sphinx\Drivers\WrapperRefreshToken;
use Hans\Sphinx\Exceptions\SphinxErrorCode;
use Hans\Sphinx\Exceptions\SphinxException;
use Hans\Sphinx\Models\Session;
use Illuminate\Contracts\Auth\Authenticatable;
use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class SphinxService
{
    /**
     * Wrapper access token provider instance.
     *
     * @var WrapperAccessToken
     */
    private WrapperAccessToken $wrapperAccessTokenProvider;

    /**
     * Inner access token provider instance.
     *
     * @var InnerAccessToken
     */
    private InnerAccessToken $innerAccessTokenProvider;

    /**
     * Wrapper refresh token provider instance.
     *
     * @var WrapperRefreshToken
     */
    private WrapperRefreshToken $wrapperRefreshTokenProvider;

    /**
     * Inner refresh token provider instance.
     *
     * @var InnerRefreshToken
     */
    private InnerRefreshToken $innerRefreshTokenProvider;

    /**
     * Related session instance.
     *
     * @var Session|null
     */
    private ?Session $session = null;

    /**
     * @throws SphinxException
     */
    public function __construct()
    {
        $this->wrapperAccessTokenProvider = new WrapperAccessToken(sphinx_config('secret'));
        $this->wrapperRefreshTokenProvider = new WrapperRefreshToken(sphinx_config('secret'));
        $this->guessSession();
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
        return $this->wrapperAccessTokenProvider->decode($token);
    }

    /**
     * Generate tokens for given user.
     *
     * @throws SphinxException
     */
    public function generateTokenFor(Authenticatable $user): self
    {
        $this->openASessionFor($user);

        $this->createAccessToken($user);
        $this->createRefreshToken($user);

        return $this;
    }

    /**
     * Return generated access token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        $this->wrapperAccessTokenProvider
            ->claim('_token', $this->innerAccessTokenProvider->getToken()->toString());

        return $this->wrapperAccessTokenProvider->getToken()->toString();
    }

    /**
     * Return generated refresh token.
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        $this->wrapperRefreshTokenProvider
            ->claim('_token', $this->innerRefreshTokenProvider->getToken()->toString());

        return $this->wrapperRefreshTokenProvider->getToken()->toString();
    }

    /**
     * Add a custom claim to the token.
     *
     * @param string           $key
     * @param string|int|array $value
     *
     * @return $this
     */
    public function claim(string $key, string|int|array $value): self
    {
        $this->innerAccessTokenProvider->claim($key, $value);

        return $this;
    }

    /**
     * Add a custom header to the token.
     *
     * @param string           $key
     * @param string|int|array $value
     *
     * @return $this
     */
    public function header(string $key, string|int|array $value): self
    {
        $this->innerAccessTokenProvider->header($key, $value);

        return $this;
    }

    /**
     * Validate wrapper access token of the given token.
     *
     * @param string $token
     *
     * @return bool
     */
    public function validateWrapperAccessToken(string $token): bool
    {
        return $this->wrapperAccessTokenProvider->validate($token);
    }

    /**
     * Assert wrapper access token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return void
     */
    public function assertWrapperAccessToken(string $token): void
    {
        $this->wrapperAccessTokenProvider->assert($token);
    }

    /**
     * Validate inner access token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return bool
     */
    public function validateInnerAccessToken(string $token): bool
    {
        if (!$this->validateWrapperAccessToken($token)) {
            return false;
        }
        $token = $this->wrapperAccessTokenProvider->decode($token);
        $insideToken = $token->claims()->get('_token');

        return $this->innerAccessTokenProvider->validate($insideToken);
    }

    /**
     * Assert inner access token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return void
     */
    public function assertInnerAccessToken(string $token): void
    {
        $this->assertWrapperAccessToken($token);
        $token = $this->wrapperAccessTokenProvider->decode($token);
        $insideToken = $token->claims()->get('_token');

        $this->innerAccessTokenProvider->assert($insideToken);
    }

    /**
     * Return inner access token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return UnencryptedToken
     */
    public function getInnerAccessToken(string $token): UnencryptedToken
    {
        $this->assertInnerAccessToken($token);
        $token = $this->wrapperAccessTokenProvider->decode($token);
        $insideToken = $token->claims()->get('_token');

        return $this->innerAccessTokenProvider->decode($insideToken);
    }

    /**
     * Validate wrapper refresh token of the given token.
     *
     * @param string $token
     *
     * @return bool
     */
    public function validateWrapperRefreshToken(string $token): bool
    {
        return $this->wrapperRefreshTokenProvider->validate($token);
    }

    /**
     * Assert wrapper refresh token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return void
     */
    public function assertWrapperRefreshToken(string $token): void
    {
        $this->wrapperRefreshTokenProvider->assert($token);
    }

    /**
     * Validate inner refresh token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return bool
     */
    public function validateInnerRefreshToken(string $token): bool
    {
        if (!$this->validateWrapperRefreshToken($token)) {
            return false;
        }
        $token = $this->wrapperRefreshTokenProvider->decode($token);
        $insideToken = $token->claims()->get('_token');

        return $this->innerRefreshTokenProvider->validate($insideToken);
    }

    /**
     * Assert inner refresh token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return void
     */
    public function assertInnerRefreshToken(string $token): void
    {
        $token = $this->wrapperRefreshTokenProvider->decode($token);
        $insideToken = $token->claims()->get('_token');

        $this->innerRefreshTokenProvider->assert($insideToken);
    }

    /**
     * Return inner refresh token of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return UnencryptedToken
     */
    public function getInnerRefreshToken(string $token): UnencryptedToken
    {
        $this->assertInnerRefreshToken($token);
        $token = $this->wrapperRefreshTokenProvider->decode($token);
        $insideToken = $token->claims()->get('_token');

        return $this->innerRefreshTokenProvider->decode($insideToken);
    }

    /**
     * Get permissions of the given token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return array
     */
    public function getPermissions(string $token): array
    {
        return $this->getInnerAccessToken($token)->claims()->get('permissions');
    }

    /**
     * Determine the token is refresh token or not.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return bool
     */
    public function isRefreshToken(string $token): bool
    {
        return $this->decode($token)->headers()->get('refresh', false);
    }

    /**
     * Determine the token is not a refresh token.
     *
     * @param string $token
     *
     * @throws SphinxException
     *
     * @return bool
     */
    public function isNotRefreshToken(string $token): bool
    {
        return !$this->isRefreshToken($token);
    }

    /**
     * Return current selected session.
     *
     * @return Session|null
     */
    public function getCurrentSession(): ?Session
    {
        return $this->session;
    }

    /**
     * Configure wrapper and inner access token instances.
     *
     * @param Authenticatable $user
     *
     * @throws SphinxException
     *
     * @return void
     */
    private function createAccessToken(Authenticatable $user): void
    {
        try {
            $this->wrapperAccessTokenProvider
                ->encode()
                ->expiresAt(sphinx_config('access_expired_at'))
                ->header('session_id', $this->session->id)
                ->header('sessionable_version', $user->getVersion())
                ->headerWhen(
                    isset($user->extractRole()['id']),
                    'role_id',
                    fn () => $user->extractRole()['id']
                )
                ->headerWhen(
                    isset($user->extractRole()['version']),
                    'role_version',
                    fn () => $user->extractRole()['version']
                );

            $this->innerAccessTokenProvider
                ->encode()
                ->claim(
                    'role',
                    collect($user->extractRole())->only('id', 'name')
                )
                ->claim(
                    'permissions',
                    collect($user->extractPermissions())
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->claim(
                    'user',
                    array_merge(
                        $user->extract(),
                        [
                            'id'              => $user->id,
                            $user->username() => $user->{$user->username()},
                        ]
                    )
                );
        } catch (Throwable $e) {
            throw new SphinxException(
                'Failed to create token! '.$e->getMessage(),
                SphinxErrorCode::FAILED_TO_CREATE_TOKEN,
                ResponseAlias::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Configure wrapper and inner refresh token instances.
     *
     * @param Authenticatable $user
     *
     * @throws SphinxException
     *
     * @return void
     */
    private function createRefreshToken(Authenticatable $user): void
    {
        try {
            $this->wrapperRefreshTokenProvider
                ->encode()
                ->expiresAt(sphinx_config('refresh_expired_at'))
                ->header('refresh', true);
            $this->innerRefreshTokenProvider
                ->encode()
                ->claim(
                    'user',
                    array_merge(
                        $user->extract(),
                        [
                            'id'              => $user->id,
                            $user->username() => $user->{$user->username()},
                        ]
                    )
                );
        } catch (Throwable $e) {
            throw new SphinxException(
                'Failed to create refresh token! '.$e->getMessage(),
                SphinxErrorCode::FAILED_TO_CREATE_REFRESH_TOKEN,
                ResponseAlias::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * Guess the related session using authorization token.
     *
     * @throws SphinxException
     *
     * @return void
     */
    private function guessSession(): void
    {
        if ($token = request()->bearerToken()) {
            $session_id = $this->wrapperAccessTokenProvider
                ->decode($token)
                ->headers()
                ->get('session_id');

            try {
                $this->session = Session::findAndCache($session_id);
            } catch (Throwable $e) {
                throw new SphinxException(
                    'Token expired! probably reached your device count limit.',
                    SphinxErrorCode::TOKEN_EXPIRED,
                    ResponseAlias::HTTP_FORBIDDEN
                );
            }

            $this->initInnerTokensInstance();
        }
    }

    /**
     * Open a new session for given user.
     *
     * @param Authenticatable $user
     *
     * @throws SphinxException
     *
     * @return void
     */
    private function openASessionFor(Authenticatable $user): void
    {
        $capturedSession = capture_session($user);
        $this->session = Session::findAndCache($capturedSession->id);

        $this->initInnerTokensInstance();
    }

    /**
     * Initialize the inner access tokens.
     *
     * @return void
     */
    private function initInnerTokensInstance(): void
    {
        $this->innerAccessTokenProvider = new InnerAccessToken($this->session->secret);
        $this->innerRefreshTokenProvider = new InnerRefreshToken($this->session->secret);
    }
}
