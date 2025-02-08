<?php

namespace Hans\Sphinx\Services;

use Hans\Sphinx\Facades\Sphinx;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;

class SphinxGuard implements AuthenticatableContract, Guard
{
    use GuardHelpers;
    use Authenticatable;
    use Macroable;

    public function __construct(
        SphinxUserProvider $provider,
        private readonly Request $request,
    ) {
        $this->provider = $provider;
        $this->loginUsingToken($request->bearerToken());
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return $this->user->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->user->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        if ($password = $this->user->{$this->getAuthPasswordName()}) {
            return $password;
        }

        return $this->provider->retrieveById($this->user->getAuthIdentifier())->getAuthPassword();
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return void
     */
    public function getRememberToken(): void
    {
        // no action needed
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     *
     * @return void
     */
    public function setRememberToken($value = null): void
    {
        // no action needed
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return void
     */
    public function getRememberTokenName(): void
    {
        // no action needed
    }

    /**
     * Get the currently authenticated user.
     *
     * @return AuthenticatableContract|null
     */
    public function user(): ?AuthenticatableContract
    {
        return $this->user ?? null;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function attempt(array $credentials): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->login($user);

            return true;
        }

        return false;
    }

    /**
     * Log the given user ID into the application.
     *
     * @param int $id
     *
     * @return AuthenticatableContract|null
     */
    public function loginUsingId(int $id): ?AuthenticatableContract
    {
        $this->user = $this->provider->retrieveById($id);

        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if (!is_null($user) and $this->provider->validateCredentials($user, $credentials)) {
            return true;
        }

        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param AuthenticatableContract $user
     *
     * @return void
     */
    public function login(AuthenticatableContract $user): void
    {
        $this->setUser($user);
    }

    /**
     * Create the user instance using validated jwt token.
     *
     * @param string|null $token
     *
     * @return void
     */
    public function loginUsingToken(?string $token): void
    {
        if ($token && Sphinx::isNotRefreshToken($token)) {
            $this->user = $this->provider
                ->retrieveByJwtTokenCredentials(
                    Sphinx::getInnerAccessToken($token)
                          ->claims()
                          ->get('user')
                );
        }
    }
}
