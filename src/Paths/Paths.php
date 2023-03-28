<?php

declare(strict_types=1);

namespace Paths;

use ast\Node;
use Paths\FunctionScanner;
use Paths\GraphNodeVisitor;

class Paths
{
    public static function fromFileName(string $file_name): \Generator
    {
        $ast = \ast\parse_file($file_name, $version = 90);
        foreach ((new FunctionScanner())($ast) as $method_ast) {
            $root = (new GraphNodeVisitor())($method_ast);
            foreach ($root->allTerminals() as $terminal) {
                foreach ($terminal->allPathsToOtherTerminals() as $path) {
                    yield $path;
                }
            }
        }
    }
}
