<?php

declare(strict_types=1);

namespace Paths;

use \ast\Node;
use Paths\AST\Visitor\KindVisitorImplementation;
use Paths\AST\Visitor\Element;

class NodeNameVisitor extends KindVisitorImplementation
{
    /**
     * The fallback implementation for node kinds where the subclass visitor
     * didn't override the more specific `visit*()` method.
     *
     * @param Node $node
     * @return mixed
     */
    public function visit(Node $node)
    {
        return Element::VISIT_LOOKUP_TABLE[$node->kind];
    }
}
