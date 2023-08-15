<?php

namespace Hans\Sphinx\Models\Contracts;

    interface Role
    {
        /**
         * Find the given id and cache the result.
         *
         * @param int $id
         *
         * @return static
         */
        public static function findAndCache(int $id): self;

        /**
         * Return version of the current instance.
         *
         * @return int
         */
        public function getVersion(): int;

        /**
         * Increase the version by one unit.
         *
         * @return bool
         */
        public function increaseVersion(): bool;
    }
