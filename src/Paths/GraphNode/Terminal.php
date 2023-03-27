<?php
declare(strict_types=1);

namespace Paths\GraphNode;

use Paths\PartialPath;
use Paths\GraphNode;

class Terminal extends GraphNode
{
    public function isTerminal(): bool
    {
        return true;
    }


    public function allPathsToOtherTerminals(): \Generator
    {
        if ($this->parent !== null) {
            foreach ($this->parent->allPathsToTerminals(new PartialPath($this)) as $path) {
                yield $path;
            }
        }
    }

    public function allPathsToTerminals(PartialPath $prefix): \Generator
    {
        yield $prefix->withTerminal($this);
    }

    public function allTerminals(): \Generator
    {
        yield $this;
    }
}