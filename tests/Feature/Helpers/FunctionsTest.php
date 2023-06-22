<?php

	namespace Hans\Sphinx\Tests\Feature\Helpers;

	use Hans\Horus\Exceptions\HorusException;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;
	use Illuminate\Support\Facades\Cache;

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
		 * @throws HorusException|SphinxException
		 */
		public function capture_sessionWithSeveralCalls(): void {
			$user        = UserFactory::createNormalUser();
			$deviceLimit = $user->getDeviceLimit();

			// hit device limit
			foreach ( range( 1, $deviceLimit ) as $counter ) {
				$sessions[] = capture_session( $user );
			}
			// capture new session
			$sessions[] = capture_session( $user );
			// old session should be deleted from DB and Cache
			self::assertCount(
				2,
				$user->sessions
			);
			$this->assertModelMissing( $sessions[ 0 ] );
			self::assertFalse( Cache::has( SphinxCache::SESSION . $sessions[ 0 ]->id ) );
			self::assertEquals(
				$sessions[ 1 ]->withoutRelations()->toArray(),
				$user->sessions[ 0 ]->toArray()
			);
			self::assertEquals(
				$sessions[ 2 ]->withoutRelations()->toArray(),
				$user->sessions[ 1 ]->toArray()
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