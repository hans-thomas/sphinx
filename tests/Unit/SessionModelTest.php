<?php

	namespace Hans\Sphinx\Tests\Unit;

	use Hans\Horus\Exceptions\HorusException;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Helpers\Enums\SphinxCache;
	use Hans\Sphinx\Models\Session;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;
	use Illuminate\Cache\ArrayStore;
	use Illuminate\Support\Facades\Cache;
	use Mockery;

	class SessionModelTest extends TestCase {

		/**
		 * @test
		 *
		 * @return void
		 * @throws HorusException
		 * @throws SphinxException
		 */
		public function create(): void {
			$user  = UserFactory::createNormalUser();
			$model = capture_session( $user );

			$this->assertModelExists( $model );
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws HorusException
		 * @throws SphinxException
		 */
		public function findAndCache(): void {
			$user  = UserFactory::createNormalUser();
			$model = capture_session( $user );

			Cache::forget( SphinxCache::SESSION . $model->id );
			self::assertNull( Cache::get( SphinxCache::SESSION . $model->id ) );

			$mock = Mockery::spy( ArrayStore::class )->makePartial();
			$mock->shouldReceive( 'forever' )->once();
			Cache::setStore( $mock );

			Session::findAndCache( $model->id );
		}

		/**
		 * @test
		 *
		 * @return void
		 * @throws HorusException
		 * @throws SphinxException
		 */
		public function getForCache(): void {
			$user  = UserFactory::createNormalUser();
			$model = capture_session( $user );

			self::assertEquals(
				(object) array_merge(
					$model->toArray(),
					[ 'sessionable_version' => $model->sessionable->getVersion() ]
				),
				$model->getForCache()
			);
		}

	}