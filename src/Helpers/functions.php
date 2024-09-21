<?php

    use DeviceDetector\ClientHints;
    use DeviceDetector\DeviceDetector;
    use Hans\Sphinx\Exceptions\SphinxErrorCode;
    use Hans\Sphinx\Exceptions\SphinxException;
    use Hans\Sphinx\Models\Session;
    use Illuminate\Contracts\Auth\Authenticatable;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;
    use Symfony\Component\HttpFoundation\Response;

    if (!function_exists('capture_session')) {
        /**
         * Capture a session for given user.
         *
         * @param Authenticatable $user
         *
         * @throws SphinxException
         *
         * @return Session
         */
        function capture_session(Authenticatable $user): Session
        {
            try {
                $deviceDetector = new DeviceDetector();
                $deviceDetector->setUserAgent(request()->userAgent());
                $deviceDetector->setClientHints(ClientHints::factory($_SERVER));
                $deviceDetector->parse();

                $client = $deviceDetector->getClient();
                $browser = '';
                if (is_array($client) && array_key_exists('name', $client)) {
                    $browser.=$client['name'];
                }
                if (is_array($client) && array_key_exists('version', $client)) {
                    $browser.=' '.$client['version'];
                }

                $os = $deviceDetector->getOs();
                $device = $deviceDetector->getDeviceName();

                if (($limit = $user->getDeviceLimit()) <= ($sessionCount = $user->sessions()->count('id'))) {
                    $ids = $user->sessions()->limit($sessionCount - ($limit - 1))->pluck('id')->toArray();
                    $user->sessions()->whereIn('id', $ids)->each(fn (Session $session) => $session->delete());
                }
                DB::beginTransaction();
                $session = $user->sessions()->create([
                    'ip'                  => request()->ip(),
                    'device'              => $device ?: 'Unknown',
                    'browser'             => $browser ?: 'Unknown',
                    'os'                  => json_encode($os),
                    'secret'              => Str::random(64),
                    'sessionable_version' => $user->getVersion(),
                ]);
                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();

                throw new SphinxException(
                    'Failed to capture current session! '.$e->getMessage(),
                    SphinxErrorCode::CAPTURE_SESSION_FAILED,
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return $session;
        }
    }

    if (!function_exists('sphinx_config')) {
        /**
         * Make it easy to access sphinx configs.
         *
         * @param string     $key
         * @param mixed|null $default
         *
         * @return mixed
         */
        function sphinx_config(string $key, mixed $default = null): mixed
        {
            return config("sphinx.$key", $default);
        }
    }

    if (!function_exists('generate_secret_key')) {
        /**
         * Generate a random secret key.
         *
         * @return mixed
         */
        function generate_secret_key(): string
        {
            return Str::random(64);
        }
    }
