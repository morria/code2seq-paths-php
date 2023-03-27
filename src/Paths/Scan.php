<?php
declare(strict_types=1);

namespace Paths;

final class Scan
{
    /**
     * 
     */
    static function filesAndDirectories(array $files_and_directories, callable $callable): void
    {
        foreach ($files_and_directories as $file_or_directory) {
            self::fileOrDirectory($file_or_directory, $callable);
        }
    }

    /**
     * 
     */
    static function fileOrDirectory(string $file_or_directory, callable $callable): void
    {
        if (is_dir($file_or_directory)) {
            self::directory($file_or_directory, $callable);
        } else {
            self::file($file_or_directory, $callable);
        }
    }

    static function directory(string $directory_name, callable $callable): void
    {
        $handle = opendir($directory_name);
        if (!$handle) {
            throw new \ErrorException("Could not open directory {$directory_name}");
        }

        while (false !== ($file_or_directory = readdir($handle))) {
            if ($file_or_directory == "." || $file_or_directory == "..") {
                continue;
            }
            self::fileOrDirectory($directory_name . DIRECTORY_SEPARATOR . $file_or_directory, $callable);
        }

        closedir($handle);
    }

    static function file(string $file_name, callable $callable): void
    {
        $callable($file_name);
    }
}