<?php
declare(strict_types=1);

namespace Paths;

use \ast\Node;
use Paths\AST\NameVisitor;
use Paths\GraphNode\Terminal;
use Paths\GraphNode\NonTerminal;


abstract class GraphNode
{
    public string $name;

    public ?NonTerminal $parent;

    public function __construct(string $name, ?GraphNode $parent = null)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public static function fromASTNode(Node $node, ?GraphNode $parent = null)
    {
        if (count($node->children) > 0) {
            return new NonTerminal($node, $parent);
        } else {
            return new Terminal((new NameVisitor())($node), $parent);
        }
    }

    abstract public function isTerminal(): bool;

    abstract public function allPathsToTerminals(PartialPath $prefix): \Generator;

    abstract public function allTerminals(): \Generator;

    public function __toString(): string
    {
        return $this->name;
    }
}