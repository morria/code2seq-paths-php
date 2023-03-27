<?php
declare(strict_types=1);

namespace Paths;

use \ast\Node;
use Paths\AST\Visitor\KindVisitorImplementation;
use Paths\AST\MethodVisitor;

class FunctionScanner extends KindVisitorImplementation
{
    /**
     * The fallback implementation for node kinds where the subclass visitor
     * didn't override the more specific `visit*()` method.
     *
     * @param Node $node
     * @return \Generator
     */
    public function visit(Node $node): \Generator
    {
        // Recurse into children
        foreach ($node->children as $child) {
            if ($child instanceof Node) {

                foreach ($this($child) as $found) {
                    yield $found;
                }
            }
        }
    }

    public function visitFuncDecl(Node $node): \Generator
    {
        yield $node;
    }

    public function visitMethod(Node $node): \Generator
    {
        yield $node;
    }
}