<?php

declare(strict_types=1);

namespace Paths;

require_once(__DIR__ . '/bootstrap.php');

use Paths\Scan;
use Paths\TargetContexts;

$rest_index = 0;
$opts = getopt('hl:s:', ['help', 'max-length:', 'seed:'], $rest_index);

if (!is_array($opts) || isset($opts['help']) || isset($opts['h'])) {
    echo <<<EOH
Emit PHP target context lists usable by code2seq.

Usage: {$argv[0]} [options] [files...]]
 -h, --help
 Get this help information

 -s SEED, --seed SEED
 The seed to use before shuffling the target contexts.

 -l LENGTH, --max-length=LENGTH
 The maximum length of a target context. Defaults to 9.
 
 ...
 All other options will be treated as file names to
 read as PHP source.

EOH;
    exit(0);
}

$max_length = intval($opts['max-length'] ?? $opts['l'] ?? 9);

$seed = $opts['seed'] ?? $opts['s'] ?? null;
if ($seed !== null) {
    $seed = intval($seed);
}

$files_and_directories = [];
if ($rest_index > 0) {
    $rest = array_slice($argv, $rest_index);
    $files_and_directories += $rest;
}

Scan::filesAndDirectories($files_and_directories, function ($file_name) use ($max_length, $seed) {
    if (!\str_ends_with($file_name, '.php')) {
        return;
    }
    foreach (TargetPaths::fromFileName($file_name, $max_length, $seed) as $target => $paths) {
        print TargetPaths::serialize(strval($target), $paths) . "\n";
    }
});
