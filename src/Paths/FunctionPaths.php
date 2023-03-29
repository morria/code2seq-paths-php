<?php

declare(strict_types=1);

namespace Paths;

use function \ast\parse_file;
use function \ast\get_version;
use Paths\FunctionScanner;
use Paths\GraphNodeVisitor;
use Paths\Path;

class FunctionPaths
{
    private string $function_name;

    /** @var array<Path> */
    private array $paths;

    /** @param $paths array<Path> */
    public function __construct(string $function_name, array $paths = [])
    {
        $this->function_name = $function_name;
        $this->paths = $paths;
    }

    public function appendPath(Path $path): void
    {
        $this->paths[] = $path;
    }

    /**
     * @return \Generator<FunctionPaths>
     */
    public static function fromFileName(string $file_name, bool $use_node_ids = false): \Generator
    {
        $ast = parse_file($file_name, get_version());
        foreach ((new FunctionScanner())($ast) as $function_ast) {
            $function_path = new FunctionPaths($function_ast->children['name'] ?? 'anonymous');
            foreach ((new GraphNodeVisitor(null, $use_node_ids))($function_ast)->allTerminals() as $terminal) {
                foreach ($terminal->allPathsToOtherTerminals() as $path) {
                    $function_path->appendPath($path);
                }
            }
            yield $function_path;
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->paths);
    }

    public function toString(?int $max_length = null): string
    {
        if ($this->isEmpty()) {
            return $this->function_name;
        }

        $paths = $this->paths;
        if ($max_length !== null) {
            shuffle($paths);
            $paths = array_slice($paths, 0, $max_length);
        }

        return implode(' ', [
            $this->function_name,
            implode(' ', array_map(fn (Path $path) => $path->__toString(), $paths))
        ]);
    }
}
