<?php

declare(strict_types=1);

namespace Paths;

error_reporting(E_ALL);
define('CLASS_DIR', __DIR__ . '../');
set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_DIR);

ini_set('memory_limit', '10240M');

foreach ([1, 4, 5] as $depth) {
    $file = dirname(__DIR__, $depth) . '/vendor/autoload.php';
    if (file_exists($file)) {
        $loader = require_once($file);
        break;
    }
}

define('EXIT_SUCCESS', 0);
define('EXIT_FAILURE', 1);

if (!\extension_loaded('ast')) {
    echo <<<EOH
ERROR: The php-ast extension must be loaded in order to run.
EOH;
    exit(1);
}

// PHP7 Polyfill
if (!function_exists('str_ends_with')) {
    function str_ends_with($str, $end)
    {
        return (@substr_compare($str, $end, -strlen($end)) == 0);
    }
}

// AST Polyfill
require_once(__DIR__ . '/ast_polyfill.php');
