<?php

declare(strict_types=1);

namespace Paths\GraphNode;

use Paths\PartialPath;
use Paths\GraphNode;
use Paths\Subtokens;

class Terminal extends GraphNode
{
    public function isTerminal(): bool
    {
        return true;
    }

    /**
     * @return \Generator<\Paths\Path>
     */
    public function allPathsToOtherTerminals(): \Generator
    {
        $parent = $this->parent;
        $prefix = new PartialPath($this);
        while ($parent != null) {
            foreach ($parent->allPathsToTerminals($prefix) as $path) {
                yield $path;
            }
            $prefix = $prefix->withNonTerminal($parent);
            $parent = $parent->parent;
        }
    }

    /**
     * @return \Generator<\Paths\Path>
     * Generate all paths to terminals reachable from this node.
     */
    public function allPathsToTerminals(PartialPath $prefix): \Generator
    {
        yield $prefix->withTerminal($this);
    }

    /**
     * @return \Generator<Terminal>
     */
    public function allTerminals(): \Generator
    {
        yield $this;
    }

    public function __toString(): string
    {
        return implode('|', Subtokens::fromString($this->name));
    }
}
