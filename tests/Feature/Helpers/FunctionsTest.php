<?php

	namespace Hans\Sphinx\Tests\Feature\Helpers;

	use Hans\Horus\Exceptions\HorusException;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;

	class FunctionsTest extends TestCase {

		/**
		 * @test
		 *
		 * @return void
		 * @throws HorusException|SphinxException
		 */
		public function capture_session(): void {
			$user    = UserFactory::createNormalUser();
			$session = capture_session( $user );

			self::assertInstanceOf(
				Session::class,
				$session
			);
			self::assertEquals(
				$user->withoutRelations()->toArray(),
				$session->sessionable->toArray()
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function sphinx_config(): void {
			$config  = require __DIR__ . '/../../../config/config.php';
			$key     = 'access_expired_at';
			$default = 'Not found!';

			self::assertStringEqualsStringIgnoringLineEndings(
				$config[ $key ],
				sphinx_config( $key )
			);

			self::assertEquals(
				$default,
				sphinx_config( 'wrong', $default )
			);
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function generate_secret_key(): void {
			self::assertIsString( generate_secret_key() );
		}

	}