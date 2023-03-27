<?php
declare(strict_types=1);

namespace Paths;

use Paths\GraphNode\Terminal;

class Path
{
    private Terminal $source;

    private array $path;

    private Terminal $target;

    public function __construct(Terminal $source, array $path, Terminal $target)
    {
        $this->source = $source;
        $this->target = $target;
        $this->path = $path;
    }

    public function __toString(): string
    {
        return implode(' ', [
            $this->source->__toString(),
            implode('|', $this->path),
            $this->target->__toString(),
        ]);

    }
}