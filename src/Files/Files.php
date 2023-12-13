<?php

/**
 * Project: Triumph Framework
 * Class: Files
 * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
 */

namespace Triumph\Files;

class Files
{
    /**
     * Get file extension
     * @param string $path
     * @return string|array
     */
    public static function getExtension(string $path): string|array
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Copy directory
     * @param string $src from
     * @param string $dest dest
     * @param array $options options
     */
    public static function copyDirectory(string $src, string $dest, array $options = []): void
    {
        $fileTypes = array();
        $exclude = array();
        $level = -1;
        // override options
        extract($options);
        // create directory if needed
        if (!is_dir($dest)) self::makeDirectory($dest, $options, true);

        // copy recursive
        self::copyDirectoryRecursive($src, $dest, '', $fileTypes, $exclude, $level, $options);
    }

    /**
     * Copy directory recursive
     * @param string $src
     * @param string $dest
     * @param $base
     * @param $fileTypes
     * @param $exclude
     * @param $level
     * @param $options
     */
    protected static function copyDirectoryRecursive(string $src, string $dest, $base, $fileTypes, $exclude, $level,
                                                            $options): void
    {
        if (!is_dir($dest)) self::makeDirectory($dest, $options, false);

        $folder = opendir($src);

        while (($file = readdir($folder)) !== false) {
            if ($file === '.' || $file === '..') continue;

            $path = $src . DIRECTORY_SEPARATOR . $file;
            $isFile = is_file($path);

            if (self::validatePath($base, $file, $isFile, $fileTypes, $exclude)) {
                if ($isFile) {
                    copy($path, $dest . DIRECTORY_SEPARATOR . $file);

                    if (isset($options['newFileMode']))
                        chmod($dest . DIRECTORY_SEPARATOR . $file, $options['newFileMode']);
                } elseif ($level)
                    self::copyDirectoryRecursive($path, $dest . DIRECTORY_SEPARATOR . $file, $base . '/' . $file,
                        $fileTypes, $exclude, $level - 1, $options);
            }

        } // while
        closedir($folder);
    }

    /**
     * Path validations
     * @param string $base
     * @param $file
     * @param $isFile
     * @param $fileTypes
     * @param $exclude
     * @return bool
     */
    protected static function validatePath(string $base, $file, $isFile, $fileTypes, $exclude): bool
    {
        foreach ($exclude as $e) {
            if ($file === $e || str_starts_with($base . '/' . $file, $e)) return false;
        }

        if (!$isFile || empty($fileTypes)) {
            return true;
        }

        if (($type = pathinfo($file, PATHINFO_EXTENSION)) !== '') {
            return in_array($type, $fileTypes);
        }
        else {
            return false;
        }
    }

    /**
     * Create directory
     * @param string $dest
     * @param mixed $options
     * @param bool $recursive
     * @return void
     */
    private static function makeDirectory(string $dest, array $options, bool $recursive): void
    {
        $prevDir = dirname($dest);

        if ($recursive && !is_dir($dest) && !is_dir($prevDir)) {
            self::makeDirectory(dirname($dest), $options, true);
        }

        $mode = $options['newDirMode'] ?? 0777;
        mkdir($dest, $mode);
        chmod($dest, $mode);
    }
}
