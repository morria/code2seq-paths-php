<?php

declare(strict_types=1);

namespace Paths;

use Paths\Paths;

class TargetPaths
{
    /**
     * @return array<string, array<Path>>
     */
    public static function fromPaths(\Generator $paths): array
    {
        $target_contexts = [];
        foreach ($paths as $path) {
            $target_name = $path->getTarget()->__toString();
            if (!isset($target_contexts[$target_name])) {
                $target_contexts[$target_name] = [];
            }
            $target_contexts[$target_name][] = $path;
        }
        return $target_contexts;
    }

    /**
     * @return array<string, array<Path>>
     */
    public static function fromFileName(string $file_name): array
    {
        return self::fromPaths(Paths::fromFileName($file_name));
    }

    /**
     * @param array<Path> $contexts
     */
    public static function serialize(string $target, array $paths): string
    {
        return $target . ' ' . implode(' ', array_map(function (Path $path): string {
            return $path->targetPathString();
        }, $paths));
    }
}
