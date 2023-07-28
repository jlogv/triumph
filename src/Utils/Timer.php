<?php

    /**
     * Project: Triumph Framework
     * Class: Timer
     * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
     */

    namespace Triumph\Utils;

    /**
     * Class for time measure
     */
    class Timer
    {
        public static float $start_time = 0;

        /**
         * @var ?Timer
         */
        protected static ?Timer $instance = null;

        /**
         * Class constructor
         */
        public function __construct()
        {
            return self::$instance;
        }

        /**
         * Class destructor
         */
        public function __destruct()
        {
            return self::$instance = null;
        }

        /**
         * Timer initialize
         */
        public static function init(): Timer
        {
            return (self::$instance === null) ? self::$instance = new self() : self::$instance;
        }
    
        /**
         * Set timer now value
         * @return float
         */
        public static function now(): float
        {
            list($uSec, $seconds) = explode(" ", microtime());
            return ((float)$uSec + (float)$seconds);
        }

        /**
         * Start measuring
         * @return float
         */
        public static function start(): float
        {
            self::$start_time = self::now();
            return self::$start_time;
        }

        /**
         * Get measuring difference
         * @return float
         */
        public static function stop(): float
        {
            return (self::now() - self::$start_time);
        }
    
        /**
         * Restart timer
         */
        public static function restart(): void
        {
            self::start();
        }
    }