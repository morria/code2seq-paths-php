<?php

declare(strict_types=1);

namespace Paths;

use \ast\Node;
use Paths\AST\NameVisitor;
use Paths\GraphNode\Terminal;
use Paths\GraphNode\NonTerminal;
use Paths\Tokens;


abstract class GraphNode
{
    public string $name;

    public ?NonTerminal $parent;

    public function __construct(string $name, ?GraphNode $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    abstract public function isTerminal(): bool;

    abstract public function allPathsToTerminals(PartialPath $prefix): \Generator;

    abstract public function allTerminals(): \Generator;

    public function __toString(): string
    {
        return implode(',', Tokens::fromString($this->name));
    }
}
