<?php

declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Paths\FunctionPaths;

final class FunctionPathsTest extends TestCase
{
    public static function caseProvider(): array
    {
        return array_reduce(glob(__DIR__ . "/cases/*.php"), function (array $carry, string $actual_file): array {
            $expected_file = str_replace(".php", ".c2s", $actual_file);
            if (file_exists($expected_file)) {
                $carry[$actual_file] = [$actual_file, $expected_file];
            }
            return $carry;
        }, []);
    }

    #[DataProvider('caseProvider')]
    public function testCases($actual_file, $expected_file): void
    {
        $actual_paths = implode("\n", array_map(function (FunctionPaths $path): string {
            return $path->toString();
        }, iterator_to_array(FunctionPaths::fromFileName($actual_file))));
        $expected_paths = trim(file_get_contents($expected_file));

        $this->assertEquals($actual_paths, $expected_paths);
    }
}
