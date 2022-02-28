<?php

	namespace Hans\Sphinx\Tests\Feature;

	use App\Models\User;
	use Hans\Sphinx\Tests\Factories\UserFactory;
	use Hans\Sphinx\Tests\TestCase;
	use Illuminate\Support\Facades\Cache;
	use SphinxCacheEnum;

	class SessionCacheTest extends TestCase {

		private User $user;

		/**
		 * Setup the test environment.
		 *
		 * @return void
		 */
		protected function setUp(): void {
			parent::setUp();
			$this->user = UserFactory::createNormalUserWithSession();
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function onCreating() {
			Cache::spy();

			$session = User::findOrFail( 1 )->sessions()->create( [
				'ip'       => 'fake data',
				'device'   => 'fake data',
				'platform' => 'fake data',
				'secret'   => 'fake data'
			] );

			Cache::shouldHaveReceived( 'forever' )
			     ->with( SphinxCacheEnum::SESSION . $session->id, $session->getForCache() );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function onUpdate() {
			$session = User::findOrFail( 1 )->sessions()->create( [
				'ip'       => 'fake data',
				'device'   => 'fake data',
				'platform' => 'fake data',
				'secret'   => 'fake data'
			] );
			Cache::spy();

			$session->update( [ 'ip' => '127.0.0.0' ] );

			Cache::shouldHaveReceived( 'forget' )->with( SphinxCacheEnum::SESSION . $session->id );
			Cache::shouldHaveReceived( 'forever' )
			     ->with( SphinxCacheEnum::SESSION . $session->id, $session->getForCache() );
		}

		/**
		 * @test
		 *
		 *
		 * @return void
		 */
		public function onDeleting() {
			$session = User::findOrFail( 1 )->sessions()->create( [
				'ip'       => 'fake data',
				'device'   => 'fake data',
				'platform' => 'fake data',
				'secret'   => 'fake data'
			] );

			Cache::spy();

			$session->delete();

			Cache::shouldHaveReceived( 'forget' )->with( SphinxCacheEnum::SESSION . $session->id );
		}

	}
