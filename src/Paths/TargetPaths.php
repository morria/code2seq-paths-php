<?php

declare(strict_types=1);

namespace Paths;

use Paths\Paths;

class TargetPaths
{
    /**
     * @return array<string, array<Path>>
     */
    public static function fromPaths(\Generator $paths, ?int $max_length = null, ?int $seed = null): array
    {
        if ($seed !== null) {
            srand($seed);
        }

        $target_paths = [];
        foreach ($paths as $path) {
            $target_name = $path->getTarget()->__toString();
            if (!isset($target_paths[$target_name])) {
                $target_paths[$target_name] = [];
            }
            $target_paths[$target_name][] = $path;
        }

        if ($max_length !== null) {
            foreach ($target_paths as $target => $paths) {
                shuffle($paths);
                $target_paths[$target] = array_slice($paths, 0, $max_length);
            }
        }

        return $target_paths;
    }

    /**
     * @return array<string, array<Path>>
     */
    public static function fromFileName(string $file_name, ?int $max_length = null, ?int $seed = null): array
    {
        return self::fromPaths(Paths::fromFileName($file_name), $max_length, $seed);
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
