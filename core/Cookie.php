<?php

namespace Core;

class Cookie
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_COOKIE[$key] ?? $default;
    }

    public static function set(string $key, string $value, int $expiry = 86400, string $path = "/", bool $secure = false, bool $httpOnly = true): void
    {
        setcookie($key, $value, time() + $expiry, $path, "", $secure, $httpOnly);
    }

    public static function has(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    public static function delete(string $key, string $path = "/"): void
    {
        setcookie($key, '', time() - 3600, $path);
        unset($_COOKIE[$key]);
    }
}
