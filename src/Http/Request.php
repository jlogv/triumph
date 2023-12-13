<?php

/**
 * Project: Triumph Framework
 * Class: Request
 * Copyright (c) Alexey Logvinov, 2014-2023. All rights reserved.
 */

namespace Triumph\Http;

use Triumph\Utils\Strings;

class Request
{
    /**
     * Host part of requested link
     * @var string
     */
    private static string $baseUrl = '';

    /**
     * Default request port
     * @var int
     */
    private static int $port = 80;

    /**
     * Default request secure port
     * @var int
     */
    private static int $securePort = 443;

    /**
     * Singleton instance
     * @var ?Request
     */
    protected static ?Request $instance = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->normalize();
        self::getBaseUrl();

        return self::$instance;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        self::$instance = null;
    }

    /**
     * Initialize the instance
     *
     * @return Request
     */
    public static function init(): Request
    {
        return (self::$instance === null) ? self::$instance = new self() : self::$instance;
    }

    /**
     * Delete extra slashes
     *
     * @return void
     */
    protected function normalize(): void
    {
        if (isset($_GET)) {
            $_GET = Strings::strip_slashes($_GET);
        }

        if (isset($_POST)) {
            $_POST = Strings::strip_slashes($_POST);
        }

        if (isset($_REQUEST)) {
            $_REQUEST = Strings::strip_slashes($_REQUEST);
        }

        if (isset($_COOKIE)) {
            $_COOKIE = Strings::strip_slashes($_COOKIE);
        }
    }

    /**
     * Get current document path
     *
     * @return string
     */
    public static function getURI(): string
    {
        return isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : '';
    }

    /**
     * Get requested object extension
     *
     * @return string
     */
    public static function getExtension(): string
    {
        // append dummy part
        $path = parse_url(self::getURI());

        // removing slashes
        $path['path'] = rtrim($path['path'], '/');

        // getting file extension
        return pathinfo($path['path'], PATHINFO_EXTENSION);
    }

    /**
     * Get current referer
     *
     * @return string
     */
    public static function getReferer(): string
    {
        return isset($_SERVER['HTTP_REFERER']) ? urldecode($_SERVER['HTTP_REFERER']) : '';
    }

    /**
     * Get current link
     *
     * @return string
     */
    public static function getURIOnly(): string
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = urldecode($_SERVER['REQUEST_URI']);
            return strtok($uri, '?');
        } else {
            return '';
        }
    }

    /**
     * Get param value from GET & POST
     *
     * @param string $name param name
     * @param mixed|null $defaultValue default value
     * @return mixed
     */
    public static function getParam(string $name, mixed $defaultValue = null): mixed
    {
        return $_GET[$name] ?? $_POST[$name] ?? $defaultValue;
    }

    /**
     * Get param from GET query
     *
     * @param mixed $name param name
     * @param mixed|null $defaultValue default value
     * @return mixed
     */
    public static function getFromQuery(string $name, mixed $defaultValue = null): mixed
    {
        return $_GET[$name] ?? $defaultValue;
    }

    /**
     * Get param from POST
     *
     * @param string $name param name
     * @param mixed|null $defaultValue default value
     * @return mixed
     */
    public static function getFromPost(string $name, mixed $defaultValue = null): mixed
    {
        return $_POST[$name] ?? $defaultValue;
    }

    /**
     * Is param exists in GET or POST
     *
     * @param string $name param name
     * @return bool if exists
     */
    public static function isParamExists(string $name): bool
    {
        if (isset($_GET[$name])) {
            return true;
        } else {
            return isset($_POST[$name]);
        }
    }

    /**
     * Get connection schema secure
     *
     * @return bool
     */
    public static function isSecure(): bool
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS']) || '1' == $_SERVER['HTTPS']) {
                return true;
            }
        }

        if (isset($_SERVER['SERVER_PORT'])) {
            if ($_SERVER['SERVER_PORT'] == 443 || $_SERVER['SERVER_PORT'] == 8443) {
                return true;
            }
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            if ($_SERVER['HTTP_X_FORWARDED_PORT'] == 443 || $_SERVER['HTTP_X_FORWARDED_PORT'] == 8443) {
                return true;
            }
        }

        if (isset($_SERVER['SSL_PROTOCOL'])) {
            if (Strings::length($_SERVER['SSL_PROTOCOL']) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current port
     *
     * @return int
     */
    public static function getPort(): int
    {
        if (self::$port === null) {
            self::$port = !self::isSecure() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
        }

        return self::$port;
    }

    /**
     * Get current secure port
     *
     * @return int
     */
    public static function getSecurePort(): int
    {
        if (self::$securePort === null) {
            self::$securePort = self::isSecure() && isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 443;
        }
        return self::$securePort;
    }

    /**
     * Get host link
     *
     * @return string
     */
    public static function getHostLink(): string
    {
        // determine schema
        $schema = ($secure = self::isSecure()) ? 'https' : 'http';

        if (isset($_SERVER['HTTP_HOST'])) {
            $result = $schema . '://' . $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $result = $schema . '://' . $_SERVER['SERVER_NAME'];
        } else {
            $result = 'localhost';
        }

        // determine port
        $port = $secure ? self::getSecurePort() : self::getPort();

        if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
            $result .= ':' . $port;
        }

        return $result;
    }

    /**
     * Get current host
     *
     * @return mixed
     */
    public static function getHost(): mixed
    {
        return $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
    }

    /**
     * Get base host url
     *
     * @return string
     */
    public static function getBaseUrl(): string
    {
        if (self::$baseUrl === '') {
            self::$baseUrl = self::getHostLink();
        }

        return self::$baseUrl;
    }

    /**
     * Get the body content
     *
     * @return string|array|bool
     */
    public static function getContent(): string|array|bool
    {
        if (empty($_POST) === false) {
            return $_POST;
        } else {
            return file_get_contents('php://input');
        }
    }

    /**
     * Get user agent string
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Check link Ajax status
     *
     * @return bool status Ajax
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Get request method
     *
     * @return string
     */
    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? '';
    }

    /**
     * Is GET method
     *
     * @return bool
     */
    public static function isGetMethod(): bool
    {
        return self::getMethod() == 'GET';
    }

    /**
     * Is POST method
     *
     * @return bool
     */
    public static function isPostMethod(): bool
    {
        return self::getMethod() == 'POST';
    }

    /**
     * Is PUT method
     *
     * @return bool
     */
    public static function isPutMethod(): bool
    {
        return self::getMethod() == 'PUT';
    }

    /**
     * Is PATCH method
     *
     * @return bool
     */
    public static function isPatchMethod(): bool
    {
        return self::getMethod() == 'PATCH';
    }

    /**
     * Is DELETE method
     *
     * @return bool
     */
    public static function isDeleteMethod(): bool
    {
        return self::getMethod() == 'DELETE';
    }

    /**
     * Determine method suffix
     *
     * @return string
     */
    public static function getMethodSuffix(): string
    {
        $method = self::getMethod();

        switch ($method) {
            case 'GET':
            {
                $result = 'GetAction';
                break;
            }
            case 'HEAD':
            {
                $result = 'HeadAction';
                break;
            }
            case 'POST':
            {
                $result = 'PostAction';
                break;
            }
            case 'PUT' :
            {
                $result = 'PutAction';
                break;
            }
            case 'DELETE' :
            {
                $result = 'DeleteAction';
                break;
            }
            case 'CONNECT' :
            {
                $result = 'ConnectAction';
                break;
            }
            case 'OPTIONS' :
            {
                $result = 'OptionsAction';
                break;
            }
            case 'TRACE' :
            {
                $result = 'TraceAction';
                break;
            }
            case 'PATCH' :
            {
                $result = 'PatchAction';
                break;
            }
            case 'PURGE' :
            {
                $result = 'PurgeAction';
                break;
            }
            default :
            {
                $result = 'Action';
            }
        }

        return $result;
    }

    /**
     * Get request headers
     *
     * @return array
     */
    public static function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_') && !str_starts_with($key, 'REDIRECT_HTTP_')) continue;

            // remove HTTP_
            $key = str_replace(['REDIRECT_HTTP_', 'HTTP_'], '', $key);

            // convert to lowercase
            $key = strtolower($key);

            // replace _ with spaces
            $key = str_replace('_', ' ', $key);

            // uppercase first char in each word
            $key = ucwords($key);

            // convert spaces to dashes
            $key = str_replace(' ', '-', $key);

            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Get client IP address
     *
     * @return string|bool
     */
    public static function getClientIP(): bool|string
    {
        if (array_key_exists("HTTP_CLIENT_IP", $_SERVER)) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        return false;
    }

    /**
     * Add custom value to GET request
     *
     * @param $name string parameter name
     * @param $value mixed value
     * @return void
     */
    public static function appendCustomGet(string $name, mixed $value): void
    {
        $_GET[$name] = $value;
    }
}
