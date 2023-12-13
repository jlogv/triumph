<?php

    /**
     * Project: Triumph Framework
     * Class: Session
     * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
     */

    namespace Triumph\Http;

    class Session
    {
        /*
         * is session started
         */
        public bool $started = false;

        /**
         * @var ?Session
         */
        protected static ?Session $instance = null;
    
        /**
         * Class constructor
         * @param boolean $autostart
         */
        public function __construct(bool $autostart = true)
        {
            if (!$this->isStarted() && $autostart)
            {
                $this->start();
            }
            return self::$instance;
        }
    
        /**
         * Class destructor
         */
        public function __destruct()
        {
            self::$instance = null;
        }

        /**
         * Use & reuse object instance
         */
        public static function init(): Session
        {
            return (self::$instance === null) ? self::$instance = new self() : self::$instance;
        }

        /**
         * Get session status
         * @return bool
         */
        public function isStarted(): bool
        {
            $this->started = isset($_SESSION);
            return $this->started;
        }
    
        /**
         * Start session
         * @return void
         */
        public function start(): void
        {
            if (!$this->started)
            {
                session_start();
                $this->started = true;
            }
        }
    
        /**
         * Close session
         * @param boolean $clearCookie delete cookie
         * @param boolean $clearData clear data
         * @return void
         */
        public function close(bool $clearCookie = true, bool $clearData = true): void
        {
            if ($this->started)
            {
                // closing cookie
                if ($clearCookie && ini_get("session.use_cookies"))
                {
                    // get cookie param from current session
                    $params = session_get_cookie_params();
                    // set cookie param
                    setcookie( session_name(), '', time() - 42000,
                        $params["path"], $params["domain"], $params["secure"], $params["httponly"] );
                }

                // clean session container
                if ($clearData) {
                    $_SESSION = array();
                }

                // delete session
                session_destroy();
                // send close
                session_write_close();
                // raise session flag
                $this->started = false;
            }
        }
    
        /**
         * Get session name
         * @return string
         */
        public function getName(): string
        {
            if ($this->started)
                return session_name();
            else
                return '';
        }
    
        /**
         * Get session ID
         * @return string
         */
        public function getId(): string
        {
            if ($this->started)
                return session_id();
            else
                return '';
        }
    
        /**
         * Change session name
         * @param string $name session name
         * @return void
         */
        public function setName(string $name): void
        {
            session_name($name);
        }
    
        /**
         * Get session param
         * @param string $name param name
         * @param mixed|null $default default value
         * @param string $namespace
         * @return mixed
         */
        public static function get(string $name, mixed $default = null, string $namespace = 'base'): mixed
        {
            $namespace = '__' . $namespace;

            if (isset($_SESSION[$namespace][$name]))
            {
                return $_SESSION[$namespace][$name];
            }
            return $default;
        }
    
        /**
         * Get param from session namespace
         * @param string $name
         * @return array
         */
        public static function getNamespace(string $name): array
        {
            $namespace = '__' . $name;
            if (isset($_SESSION[$namespace]))
            {
                return $_SESSION[$namespace];
            }
            return [];
        }
    
        /**
         * Set session value in namespace
         * @param string $name name
         * @param mixed|null $value value
         * @param string $namespace
         * @return mixed
         */
        public static function set(string $name, mixed $value = null, string $namespace = 'base'): mixed
        {
            $namespace = '__' . $namespace;

            // get current value
            $old = $_SESSION[$namespace][$name] ?? null;

            if (null === $value)
            {
                unset($_SESSION[$namespace][$name]);
            }
            else
            {
                $_SESSION[$namespace][$name] = $value;
            }

            // return old value
            return $old;
        }
    
        /**
         * Set session params with array
         * @param string $name namespace
         * @param mixed $array array
         * @return boolean
         */
        public function setNamespace(string $name, mixed $array): bool
        {
            $namespace = '__' . $name;
            $_SESSION[$namespace] = $array;
            return true;
        }

        /**
         * Create token to prevent CSRF
         * @return string
         */
        public static function createToken() : string
        {
            $token = sha1(mt_rand());
            self::set($token, 1, 'tokens');

            return $token;
        }

        /**
         * Check stored token
         * @param $token
         * @return bool
         */
        public static function checkToken($token) : bool
        {
            $value = self::get($token, 0, 'tokens');
            return $value == 1;
        }

        /**
         * Delete token
         * @param $token
         */
        public static function deleteToken($token): void
        {
            self::set($token, null, 'tokens');
        }
    }
