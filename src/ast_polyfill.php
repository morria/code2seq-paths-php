<?php

namespace ast;

function get_version(): int
{
    return explode('.', PHP_VERSION)[0] > 7 ? 90 : 80;
}

foreach ([
    'ast\\AST_TYPE_INTERSECTION' => 145,
    'ast\\AST_ENUM_CASE' => 1026,
    'ast\\AST_CALLABLE_CONVERT' => 3,
] as $const => $value) {
    if (!\defined($const)) {
        define($const, 145);
    }
}
