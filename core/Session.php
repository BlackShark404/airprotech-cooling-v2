<?php

namespace Core;

class Session
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        session_unset();
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    public static function flash(string $key, mixed $message = null): mixed
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key][] = $message; 
            return null;
        }

        $messages = $_SESSION['_old_flash'][$key] ?? [];
        unset($_SESSION['_old_flash'][$key]);
        return $messages;
    }

}
