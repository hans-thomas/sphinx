<?php

namespace Hans\Sphinx\Services;

    use Illuminate\Auth\EloquentUserProvider;
    use Illuminate\Contracts\Auth\Authenticatable;
    use Illuminate\Database\Eloquent\Model;

    class SphinxUserProvider extends EloquentUserProvider
    {
        /**
         * Retrieve a user by their unique identifier and "remember me" token.
         *
         * @param mixed  $identifier
         * @param string $token
         *
         * @return Authenticatable|null
         */
        public function retrieveByToken($identifier, $token = null): ?Authenticatable
        {
            return $this->retrieveById($identifier);
        }

        /**
         * Update the "remember me" token for the given user in storage.
         *
         * @param Authenticatable|null $user
         * @param null                 $token
         *
         * @return void
         */
        public function updateRememberToken(Authenticatable $user = null, $token = null): void
        {
        }

        /**
         * @param array $credentials
         *
         * @return Model|null
         */
        public function retrieveByJwtTokenCredentials(array $credentials): ?Model
        {
            $instance = $this->createModel();
            if (!isset($credentials[$instance->getAuthIdentifierName()])) {
                return null;
            }

            $instance->fill($credentials);
            $instance->{$instance->getAuthIdentifierName()} = $credentials[$instance->getAuthIdentifierName()];
            $instance->exists = true;

            return $instance;
        }
    }
