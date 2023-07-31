<?php

namespace Hans\Sphinx\Drivers\Constraints;

    use Hans\Sphinx\Exceptions\SphinxErrorCode;
    use Hans\Sphinx\Exceptions\SphinxException;
    use Hans\Sphinx\Models\Session;
    use Lcobucci\JWT\Token;
    use Lcobucci\JWT\Validation\Constraint;
    use Symfony\Component\HttpFoundation\Response as ResponseAlias;

    final class SessionIdValidator implements Constraint
    {
        /**
         * @param Token $token
         *
         * @throws SphinxException
         */
        public function assert(Token $token): void
        {
            $session_id = $token->headers()->get('session_id', false);
            $sessionable_version = $token->headers()->get('sessionable_version', false);

            if (!$session_id) {
                throw new SphinxException(
                    'Session id not found in header!',
                    SphinxErrorCode::SESSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN
                );
            }
            if (!$sessionable_version) {
                throw new SphinxException(
                    "User's version not found in header!",
                    SphinxErrorCode::USERS_VERSION_NOT_FOUND,
                    ResponseAlias::HTTP_FORBIDDEN
                );
            }

            $session = Session::findAndCache($session_id);

            if ($session->sessionable_version != $sessionable_version) {
                throw new SphinxException(
                    'Token is out-of-date!',
                    SphinxErrorCode::TOKEN_IS_OUT_OF_DATE,
                    ResponseAlias::HTTP_FORBIDDEN
                );
            }
        }
    }
