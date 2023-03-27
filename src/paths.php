<?php
declare(strict_types=1);

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

if (isset($opts['directory'])) {
    if (is_array($opts['directory'])) {
        $files_and_directories += $opts['directory'];
    } else {
        $files_and_directories += $opts['directory'];
    }
}



print_r($files_and_directories);