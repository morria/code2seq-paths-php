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
        $p = $this->parent;

        while ($p != null) {
            foreach ($p->allPathsToTerminals(new PartialPath($this)) as $path) {
                yield $path;
            }
            $p = $p->parent;
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
