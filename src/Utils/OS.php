<?php

/**
 * Project: Triumph Framework
 * Class: OS
 * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
 */

namespace Triumph\Utils;

class OS
{
    /**
     * Windows
     */
    public const OS_WIN = 1;

    /**
     * Linux, Unix
     */
    public const OS_NIX = 2;

    /**
     * Macintosh
     */
    public const OS_MAC = 3;

    /**
     * Check is Windows operating system
     * @return bool
     */
    public static function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR == '\\';
    }

    /**
     * Check is nix operating system
     * @return bool
     */
    public static function isLinux(): bool
    {
        return DIRECTORY_SEPARATOR != '\\';
    }

    /**
     * Get current type of operating system
     * @return int
     */
    public static function getOS(): int
    {
        $result = 0;
        if (self::isWindows()) {
            return self::OS_WIN;
        }
        if (self::isLinux()) {
            return self::OS_NIX;
        }
        return $result;
    }
}
