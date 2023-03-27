<?php
declare(strict_types=1);

namespace Paths;

use \ast\Node;
use Paths\Path;
use Paths\GraphNode;
use Paths\GraphNode\Terminal;
use Paths\GraphNode\NonTerminal;

class PartialPath
{
    private Terminal $source;

    private array $path;

    public function __construct(Terminal $source, array $path = [])
    {
        $this->source = $source;
        $this->path = $path;
    }

    public function withTerminal(Terminal $target): Path
    {
        return new Path($this->source, $this->path, $target);
    }

    public function withNonTerminal(NonTerminal $node): PartialPath
    {
        return new PartialPath($this->source, $this->path + [$node]);
    }

    public function previousNode(): GraphNode
    {
        if (empty($this->path)) {
            return $this->source;
        }
        return $this->path[array_key_last($this->path)];
    }
}