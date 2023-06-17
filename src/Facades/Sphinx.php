<?php

	namespace Hans\Sphinx\Facades;

	use Illuminate\Support\Facades\Facade;
	use RuntimeException;

	class Sphinx extends Facade {

		/**
		 * Get the registered name of the component.
		 *
		 * @return string
		 *
		 * @throws RuntimeException
		 */
		protected static function getFacadeAccessor() {
			return 'sphinx-service';
		}

	}