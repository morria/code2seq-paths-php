<?php

declare(strict_types=1);

namespace Paths;

final class Subtokens
{
    /**
     * Split on whitespace, camel case, kabab case, and snake case.
     * Normalize to lowercase.
     */
    public static function fromString(string $string): array
    {

        // Replace special characters
        $string = str_replace([',', '|', '\\', '"', "'"], ['comma', 'pipe', 'slash', 'quote', "quote"], $string);

        // Collapse whitespace
        $string = trim(preg_replace('/\s+/', ' ', $string));

        // Remove non-ascii
        $string = preg_replace('/[[:^print:]]/', '', $string);

        // Replace all-caps `FOO` with `Foo`.
        $string = preg_replace_callback('/([A-Z])([A-Z]+)/', function ($matches) {
            return $matches[1] . strtolower($matches[2]);
        }, $string);

        // Tokenize on whitespace, camel case, kabab case, and snake case.
        $tokens = array_values(array_filter(array_map(function (string $token): string {
            return str_replace(['-', '_'], '', trim(strtolower($token)));
        }, preg_split('/(?=[A-Z\s_-])/', $string)), function (string $token): bool {
            return $token !== '';
        }));

        return $tokens;
    }
}