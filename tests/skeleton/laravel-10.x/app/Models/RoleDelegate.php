<?php

namespace App\Models;

use Hans\Horus\Helpers\Enums\CacheEnum;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Throwable;

// TODO: not documented in horus
class RoleDelegate extends Role
{
    /**
     * @param int $id
     *
     * @return static
     */
    public static function findAndCache(int $id): self
    {
        return Cache::rememberForever(
            self::cacheKey($id),
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
     * @param int $id
     *
     * @return string
     */
    private static function cacheKey(int $id): string
    {
        return CacheEnum::ROLE->value."_$id";
    }
}
