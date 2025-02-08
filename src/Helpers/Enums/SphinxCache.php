<?php

namespace Hans\Sphinx\Helpers\Enums;

enum SphinxCache: string
{
    public const SESSION = 'sphinx_session_cache_';
    public const VERSION = 'sphinx_user_version_cache_';
}
