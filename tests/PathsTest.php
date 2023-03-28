<?php

declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Paths\Path;
use Paths\Paths;

final class PathsTest extends TestCase
{
    public static function caseProvider(): array
    {
        return array_reduce(glob(__DIR__ . "/cases/*.php"), function (array $carry, string $actual_file): array {
            $carry[$actual_file] = [
                $actual_file,
                str_replace(".php", ".txt", $actual_file)
            ];
            return $carry;
        }, []);
    }

    #[DataProvider('caseProvider')]
    public function testCases($actual_file, $expected_file): void
    {
        $actual_paths = implode("\n", array_map(function (Path $path): string {
            return $path->__toString();
        }, iterator_to_array(Paths::fromFileName($actual_file))));
        $expected_paths = trim(file_get_contents($expected_file));

        $this->assertEquals($actual_paths, $expected_paths);
    }
}
