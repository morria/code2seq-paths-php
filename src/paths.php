<?php

declare(strict_types=1);

namespace Paths;

require_once(__DIR__ . '/bootstrap.php');

use Paths\Scan;
use Paths\FunctionPaths;

$rest_index = 0;
$opts = getopt('h', ['help'], $rest_index);

if (!is_array($opts) || isset($opts['help'])) {
    echo <<<EOH
Emit PHP source paths usable by code2seq.

Usage: {$argv[0]} [options] [files...]]
 -h, --help
 Get this help information

 -s SEED, --seed SEED
 The seed to use before shuffling the target contexts.

 -l LENGTH, --max-length=LENGTH
 The maximum length of a target context. Defaults to unbounded.
 
 ...
 All other options will be treated as file names to
 read as PHP source.

EOH;
    exit(0);
}

$seed = $opts['seed'] ?? $opts['s'] ?? null;
if ($seed !== null) {
    $seed = intval($seed);
    srand($seed);
}

$max_length = $opts['max-length'] ?? $opts['l'] ?? null;
if ($max_length !== null) {
    $max_length = intval($max_length);
}

$files_and_directories = [];
if ($rest_index > 0) {
    $rest = array_slice($argv, $rest_index);
    $files_and_directories += $rest;
}

Scan::filesAndDirectories($files_and_directories, function ($file_name) use ($max_length) {
    if (!\str_ends_with($file_name, '.php')) {
        return;
    }
    foreach (FunctionPaths::fromFileName($file_name) as $function_paths) {
        if ($function_paths->isEmpty()) {
            continue;
        }
        print $function_paths->toString($max_length) . "\n";
    }
});
