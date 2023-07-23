<?php

    /**
     * Project: Triumph Framework
     * Class: Strings
     * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
     */

    namespace Triumph\Utils;

    use Triumph\Http\Request;

    class Strings
    {
        /**
         * Multibyte string to lower
         *
         * @param string $string
         * @return string
         */
        public static function lower(string $string) : string
        {
            return mb_strtolower($string);
        }

        /**
         * Multibyte string to upper
         *
         * @param string $string
         * @return string
         */
        public static function upper(string $string) : string
        {
            return mb_strtoupper($string);
        }

        /**
         * Check is valid JSON string
         *
         * @param string $string
         * @return bool
         */
        public static function isJson(string $string) : bool
        {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        /**
         * Get multibyte string length
         *
         * @param string $value string
         * @return int length
         */
        public static function length(string $value): int
        {
            return mb_strlen($value);
        }


        /**
         * Delete extra slashes then magic methods used
         *
         * @param mixed $data used data
         * @return array|string clear data
         */
        public static function strip_slashes(mixed $data): array|string
        {
            return is_array($data) ? array_map(array('self::strip_slashes'), $data) : stripslashes($data);
        }
    }