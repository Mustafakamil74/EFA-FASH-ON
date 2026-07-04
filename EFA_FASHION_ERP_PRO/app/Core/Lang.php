<?php

namespace App\Core;

/**
 * Lightweight translation manager backed by PHP arrays in /lang.
 * Falls back to English, then to the key itself.
 */
class Lang
{
    private static string $current = 'en';
    private static array $messages = [];
    private static array $fallback = [];

    public static function boot(string $lang): void
    {
        $langs = config('app.langs', ['en']);
        if (!in_array($lang, $langs, true)) {
            $lang = config('app.default_lang', 'en');
        }
        self::$current  = $lang;
        self::$messages = self::loadFile($lang);
        self::$fallback = self::loadFile('en');
    }

    private static function loadFile(string $lang): array
    {
        $path = base_path('lang/' . $lang . '.php');
        return is_file($path) ? (array) require $path : [];
    }

    public static function get(string $key, array $replace = []): string
    {
        $text = self::$messages[$key] ?? self::$fallback[$key] ?? $key;
        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, (string) $v, $text);
        }
        return $text;
    }

    public static function current(): string
    {
        return self::$current;
    }

    public static function isRtl(): bool
    {
        return in_array(self::$current, config('app.rtl_langs', []), true);
    }

    public static function dir(): string
    {
        return self::isRtl() ? 'rtl' : 'ltr';
    }
}
