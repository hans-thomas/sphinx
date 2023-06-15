<?php

	namespace Hans\Sphinx\Traits;

	use Hans\Sphinx\Models\Session;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	trait SphinxRelationHandler {
		/**
		 * Access the user's sessions through account
		 *
		 * @return HasMany
		 */
		public function sessions(): HasMany {
			// TODO: morphTo
			return $this->hasMany( Session::class );
		}
	}