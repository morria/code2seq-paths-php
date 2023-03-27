<?php
declare(strict_types=1);

namespace Paths;

use ast\Node;
use Paths\AST\Visitor;

class Paths
{
    private array $paths;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    static function fromFileName(string $file_name): Paths
    {
        $visitor = new Visitor();
        $root_node = \ast\parse_file($file_name, $version = 90);
        print_r($root_node);
        $visitor($root_node);

        return new Paths([]);
    }

    public function __toString()
    {
        return 'yo';
    }
}