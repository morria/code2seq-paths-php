<?php

declare(strict_types=1);

namespace Paths;

require_once(__DIR__ . '/bootstrap.php');

use Paths\Scan;
use Paths\Paths;

$rest_index = 0;
$opts = getopt('h', ['help'], $rest_index);

if (!is_array($opts) || isset($opts['help'])) {
    echo <<<EOH
Emit PHP source paths usable by code2seq.

Usage: {$argv[0]} [options] [files...]]
 -h, --help
 Get this help information
 
 ...
 All other options will be treated as file names to
 read as PHP source.

EOH;
    exit(0);
}

$files_and_directories = [];
if ($rest_index > 0) {
    $rest = array_slice($argv, $rest_index);
    $files_and_directories += $rest;
}

Scan::filesAndDirectories($files_and_directories, function ($file_name) {
    if (!\str_ends_with($file_name, '.php')) {
        return;
    }
    foreach (Paths::fromFileName($file_name) as $path) {
        print $path . "\n";
    }
});
