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
    private array $paths = [];

    private ?int $max_length = null;
    private ?float $sample_rate = null;

    /** @param $paths array<Path> */
    public function __construct(string $function_name, ?int $max_length = null)
    {
        $this->function_name = $function_name;
        $this->max_length = $max_length;
    }

    public function appendPath(Path $path): void
    {
        if ($this->sample_rate === null) {
            $this->paths[] = $path;
        } else {
            if (rand(0, 1000) / 1000.0 <= $this->sample_rate) {
                $this->paths[] = $path;
            }
        }

        // Once we break 2x the max paths, randomly cut it in half and start
        // sampling at 50%
        if ($this->max_length !== null) {
            if (count($this->paths) == (2 * $this->max_length)) {
                // Cut the sample rate in half
                $this->sample_rate = ($this->sample_rate ?? 1.0) / 2.0;

                // Trim off a random 50% of existing paths. This will
                // produce the same fair distribution as if the sample
                // rate had been it's current value from the beginning.
                $paths = $this->paths;
                shuffle($paths);
                $this->paths = array_values(array_slice($paths, 0, $this->max_length));
            }
        }
    }

    /**
     * @return \Generator<FunctionPaths>
     */
    public static function fromFileName(string $file_name, bool $use_node_ids = false, ?int $max_length = null): \Generator
    {
        $ast = parse_file($file_name, get_version());
        foreach ((new FunctionScanner())($ast) as $function_ast) {
            $function_path = new FunctionPaths($function_ast->children['name'] ?? 'anonymous', $max_length);
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

    public function toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $paths = $this->paths;
        if ($this->max_length !== null && count($paths) > $this->max_length) {
            shuffle($paths);
            $paths = array_slice($paths, 0, $this->max_length);
        }

        return implode(' ', [
            $this->function_name,
            implode(' ', array_map(fn (Path $path) => $path->__toString(), $paths))
        ]);
    }
}
