<?php
declare(strict_types=1);

namespace Paths\AST;

use \ast\Node;
use Paths\AST\Visitor\KindVisitorImplementation;
use Paths\AST\MethodVisitor;

class MethodVisitor extends KindVisitorImplementation
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
        return;
    }

    public function visitFuncDecl(Node $node)
    {
        print_r($node);
    }
}