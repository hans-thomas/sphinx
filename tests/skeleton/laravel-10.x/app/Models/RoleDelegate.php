<?php

namespace App\Models;

use Hans\Sphinx\Models\Contracts\RoleMethods as RoleContract;
use Hans\Sphinx\Models\Traits\RoleMethods;
use Spatie\Permission\Models\Role;

class RoleDelegate extends Role implements RoleContract
{
use RoleMethods;
}
