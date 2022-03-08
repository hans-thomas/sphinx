<?php

	use Hans\Sphinx\Exceptions\SphinxErrorCode;
	use Hans\Sphinx\Exceptions\SphinxException;
	use Hans\Sphinx\Models\Session;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Str;
	use Jenssegers\Agent\Facades\Agent;
	use Symfony\Component\HttpFoundation\Response;

	if ( ! function_exists( 'capture_session' ) ) {
		/**
		 * @throws SphinxException
		 */
		function capture_session(): Session|bool {
			try {
				$browser = rtrim( ( $browser = Agent::browser() ) . ( ' ' . Agent::version( $browser ) ? : '' ) );
				$os      = rtrim( ( $os = Agent::platform() ) . ( ' ' . Agent::version( $os ) ? : null ) );
				$user    = auth()->user();
				if ( ( $limit = $user->getDeviceLimit() ) <= ( $sessionCount = $user->sessions()->count( 'id' ) ) ) {
					$ids = $user->sessions()->limit( $sessionCount - ( $limit - 1 ) )->pluck( 'id' )->toArray();
					$user->sessions()->whereIn( 'id', $ids )->each( fn( Session $session ) => $session->delete() );
				}
				DB::beginTransaction();
				$session = $user->sessions()->create( [
					'ip'       => request()->ip(),
					'device'   => $os . ' ' . $browser,
					'platform' => Agent::device() ? : 'Unknown',
					'secret'   => Str::random( 64 )
				] );
				DB::commit();
			} catch ( Throwable $e ) {
				DB::rollBack();
				throw new SphinxException( $e->getMessage(), SphinxErrorCode::CAPTURE_SESSION_FAILED,
					Response::HTTP_INTERNAL_SERVER_ERROR );
			}

			return $session;
		}
	}

