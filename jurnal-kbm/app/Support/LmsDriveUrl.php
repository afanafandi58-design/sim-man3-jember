<?php

namespace App\Support;

class LmsDriveUrl
{
    /** @var list<string> */
    public const ALLOWED_HOSTS = [
        'drive.google.com',
        'docs.google.com',
        'forms.gle',
        'forms.google.com',
    ];

    public static function isValid(?string $url): bool
    {
        if (! is_string($url) || trim($url) === '') {
            return false;
        }

        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host)) {
            return false;
        }

        $host = strtolower($host);

        foreach (self::ALLOWED_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return str_starts_with(strtolower($url), 'https://');
            }
        }

        return false;
    }

    public static function normalize(string $url): string
    {
        return trim($url);
    }
}
