<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Shape Arabic text for DomPDF (no native Arabic joining).
 * Uses bundled ArPHP Glyphs (LGPL) via require_once — no Composer install needed on host.
 *
 * utf8Glyphs() returns visual-order presentation forms. DomPDF cells must NOT
 * apply unicode-bidi:bidi-override on the result (that would reverse again).
 */
class ArabicPdfText
{
    private static bool $loaded = false;

    private static ?\ArPHP\I18N\Arabic $engine = null;

    public static function shape(?string $text, int $maxChars = 120): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (! preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }

        try {
            self::ensureLoaded();

            $glyphs = app_path('Libraries/ar-php/data/ar_glyphs.json');
            if (! is_readable($glyphs)) {
                throw new \RuntimeException('ar_glyphs.json missing: '.$glyphs);
            }

            $normalized = class_exists(\Normalizer::class)
                ? (\Normalizer::normalize($text, \Normalizer::FORM_C) ?: $text)
                : $text;

            // forcertl=true: treat mixed lines as RTL for PDF visual stream
            return self::$engine->utf8Glyphs($normalized, $maxChars, true, true);
        } catch (\Throwable $e) {
            Log::warning('ArabicPdfText::shape failed', [
                'message' => $e->getMessage(),
                'sample' => mb_substr($text, 0, 40),
            ]);

            return $text;
        }
    }

    /** Absolute path DomPDF can load for @font-face. */
    public static function amiriFontPath(): string
    {
        $path = storage_path('fonts/Amiri-Regular.ttf');

        return str_replace('\\', '/', $path);
    }

    private static function ensureLoaded(): void
    {
        if (self::$engine instanceof \ArPHP\I18N\Arabic) {
            return;
        }

        if (! class_exists(\ArPHP\I18N\Arabic::class, false)) {
            $path = app_path('Libraries/ar-php/Arabic.php');
            if (! is_readable($path)) {
                throw new \RuntimeException('Arabic.php missing: '.$path);
            }
            require_once $path;
        }

        self::$engine = new \ArPHP\I18N\Arabic;
        self::$loaded = true;
    }
}
