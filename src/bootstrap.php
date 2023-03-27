<?php
declare(strict_types=1);

namespace Paths;

error_reporting(E_ALL);
define('CLASS_DIR', __DIR__ . '../');
set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_DIR);

foreach ([dirname(__DIR__, 1) . '/vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        $loader = require_once($file);
        break;
    }
}

define('EXIT_SUCCESS', 0);
define('EXIT_FAILURE', 1);