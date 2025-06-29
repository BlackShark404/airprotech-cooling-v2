<?php

namespace Core;

use HTMLPurifier;
use HTMLPurifier_Config;

class Sanitize
{
    private HTMLPurifier $purifier;

    public function __construct(array $configOptions = [])
    {
        $config = HTMLPurifier_Config::createDefault();
        foreach ($configOptions as $key => $value) {
            $config->set($key, $value);
        }

        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitizeHTML(string $input): string
    {
        return $this->purifier->purify($input);
    }

    public function sanitizeText(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function sanitizeEmail(string $email): string|false
    {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ?: false;
    }

    public function sanitizeURL(string $url): string|false
    {
        $url = filter_var(trim($url), FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ?: false;
    }

    public function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize each value in an associative array.
     *
     * @param array $data The input array (from JSON/form).
     * @param array $rules Key => type (e.g., ['email' => 'email', 'message' => 'html'])
     * @return array Sanitized array.
     */
    public function sanitizeArray(array $data, array $rules): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            if (!isset($rules[$key])) {
                $clean[$key] = $this->sanitizeText((string) $value); // default fallback
                continue;
            }

            switch ($rules[$key]) {
                case 'html':
                    $clean[$key] = $this->sanitizeHTML((string) $value);
                    break;
                case 'text':
                    $clean[$key] = $this->sanitizeText((string) $value);
                    break;
                case 'email':
                    $clean[$key] = $this->sanitizeEmail((string) $value);
                    break;
                case 'url':
                    $clean[$key] = $this->sanitizeURL((string) $value);
                    break;
                case 'int':
                    $clean[$key] = $this->sanitizeInt($value);
                    break;
                case 'float':
                    $clean[$key] = $this->sanitizeFloat($value);
                    break;
                default:
                    $clean[$key] = $this->sanitizeText((string) $value);
            }
        }

        return $clean;
    }
}
