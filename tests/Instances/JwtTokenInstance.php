<?php

namespace Hans\Sphinx\Tests\Instances;

use Hans\Sphinx\Drivers\Constraints\ExpirationValidator;
use Hans\Sphinx\Drivers\Contracts\JwtToken;

class JwtTokenInstance extends JwtToken
{
    /**
     * @return array
     */
    protected function getAvailableConstrains(): array
    {
        return [
            new ExpirationValidator(),
        ];
    }
}
