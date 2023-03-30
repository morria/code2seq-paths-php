<?php

declare(strict_types=1);

namespace Paths\GraphNode;

use Paths\PartialPath;
use Paths\Path;
use Paths\GraphNode;

class NonTerminal extends GraphNode
{
    private array $children = [];

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function appendChild(GraphNode $child): void
    {
        $this->children[] = $child;
    }

    public function isTerminal(): bool
    {
        return false;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return \Generator<Path>
     * Generate all paths to all terminals reachable from
     * this node.
     */
    public function allPathsToTerminals(PartialPath $prefix): \Generator
    {
        $previous_node = $prefix->lastNode();
        $prefix = $prefix->withNonTerminal($this);

        // Only follow "forward" paths by only going to children with
        // greater indices than the previous node on the path.
        //
        // n.b.: This assumes that the indices of `$children` are
        // monotonically incrementing by 1. If this ever isn't the case
        // we'll need to call `array_values` on it.
        $starting_index = array_search($previous_node, $this->children, $strict = true);
        if ($starting_index === false) {
            $starting_index = -1;
        }

        if ($starting_index + 1 <= count($this->children) - 1) {
            foreach (range($starting_index + 1, count($this->children) - 1) as $i) {
                yield from $this->children[$i]->allPathsToTerminals($prefix);
            }
        }

        /*
        foreach ($this->children as $child) {
            // Skip the node we just popped out of
            if ($child === $previous_node) {
                continue;
            }
            yield from $child->allPathsToTerminals($prefix);
        }
        */
    }

    /**
     * 
     * @return \Generator<Terminal>
     * Generate all the terminal nodes reachable from this node
     */
    public function allTerminals(): \Generator
    {
        foreach ($this->children as $child) {
            yield from $child->allTerminals();
        }
    }
}
