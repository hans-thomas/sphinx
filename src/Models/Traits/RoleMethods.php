<?php

namespace Hans\Sphinx\Models\Traits;

    use Illuminate\Support\Facades\Cache;
    use Throwable;

    trait RoleMethods
    {
        /**
         * @param int $id
         *
         * @return static
         */
        public static function findAndCache(int $id): self
        {
            return Cache::rememberForever(
                static::cacheKey($id),
                fn () => self::query()->findOrFail($id)
            );
        }

        /**
         * @return int
         */
        public function getVersion(): int
        {
            return $this->version ?: self::query()->findOrFail($this->id, ['id', 'version'])->version;
        }

        /**
         * @return bool
         */
        public function increaseVersion(): bool
        {
            try {
                $this->increment('version');
                $this->fill(['version' => $this->getVersion() + 1])->saveQuietly();
                Cache::forget(self::cacheKey($this->id));
                Cache::forever(self::cacheKey($this->id), $this);
            } catch (Throwable $e) {
                return false;
            }

            return true;
        }

        /**
         * Make the unique key for caching the instance.
         *
         * @param int $id
         *
         * @return string
         */
        protected static function cacheKey(int $id): string
        {
            return "role_cache_$id";
        }
    }
