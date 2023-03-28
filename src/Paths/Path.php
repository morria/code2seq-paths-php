<?php

declare(strict_types=1);

namespace Paths;

use Paths\GraphNode\Terminal;

class Path
{
    private Terminal $source;

    /**
     * @var array<\Paths\GraphNode\NonTerminal>
     */
    private array $path;

    private Terminal $target;

    /**
     * @param $path array<\Paths\GraphNode\NonTerminal>
     */
    public function __construct(Terminal $source, array $path, Terminal $target)
    {
        $this->source = $source;
        $this->target = $target;
        $this->path = $path;
    }

    public function getSource(): Terminal
    {
        return $this->source;
    }

    public function getTarget(): Terminal
    {
        return $this->target;
    }

    /** @return array<\Paths\GraphNode\NonTerminal> */
    public function getPath(): array
    {
        return $this->path;
    }

    public function __toString(): string
    {
        return implode(',', [
            $this->source->__toString(),
            implode('|', $this->path),
            $this->target->__toString(),
        ]);
    }
}
