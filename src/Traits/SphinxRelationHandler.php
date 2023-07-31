<?php

namespace Hans\Sphinx\Traits;

use Hans\Sphinx\Models\Session;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait SphinxRelationHandler
{
    /**
     * Relationship definition with session model.
     *
     * @return MorphMany
     */
    public function sessions(): MorphMany
    {
        return $this->morphMany(Session::class, 'sessionable');
    }
}
