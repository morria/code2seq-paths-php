<?php

declare(strict_types=1);

namespace Paths;

final class Tokens
{

    /**
     * Split on whitespace, camel case, kabab case, and snake case.
     * Normalize to lowercase.
     */
    public static function fromString(string $string): array
    {
        $tokens = preg_split('/(?=[A-Z\s_-])/', $string);
        $tokens = array_values(array_filter(array_map(function (string $token): string {
            return str_replace(['-', '_'], '', trim(strtolower($token)));
        }, $tokens), function (string $token): bool {
            return $token !== '';
        }));
        return $tokens;
    }
}
