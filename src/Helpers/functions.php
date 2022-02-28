<?php

	if ( ! function_exists( 'capture_session' ) ) {
		function capture_session(): \Hans\Sphinx\Models\Session|bool {
			try {
				$browser = rtrim( ( $browser = Agent::browser() ) . ( ' ' . Agent::version( $browser ) ? : '' ) );
				$os      = rtrim( ( $os = Agent::platform() ) . ( ' ' . Agent::version( $os ) ? : null ) );
				if ( method_exists( user(), 'getDeviceLimit' ) ) {
					if ( ( $limit = user()->getDeviceLimit() ) <= user()->sessions()->count( 'id' ) ) {
						$ids = user()->sessions->take( count( user()->sessions ) - ( $limit - 1 ) )
						                       ->pluck( 'id' )
						                       ->toArray();
						user()->sessions()->whereIn( 'id', $ids )->delete();
					}
				}
				DB::beginTransaction();
				$session = Auth::user()->sessions()->create( [
					'ip'       => request()->ip(),
					'device'   => $os . ' ' . $browser,
					'platform' => Agent::device() ? : 'Unknown',
					'secret'   => \Illuminate\Support\Str::random( 64 )
				] );
				DB::commit();
			} catch ( \Throwable $e ) {
				DB::rollBack();
				Log::error( 'capture_session ->', [ 'message' => $e->getMessage() ] );

				return false;
			}

			return $session;
		}
	}

