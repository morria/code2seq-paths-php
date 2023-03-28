<?php

declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Paths\Path;
use Paths\TargetPaths;

final class TargetPathsTest extends TestCase
{
    public static function caseProvider(): array
    {
        return array_reduce(glob(__DIR__ . "/cases/*.php"), function (array $carry, string $actual_file): array {
            $expected_file = str_replace(".php", ".tp", $actual_file);
            if (file_exists($expected_file)) {
                $carry[$actual_file] = [$actual_file, $expected_file];
            }
            return $carry;
        }, []);
    }

    #[DataProvider('caseProvider')]
    public function testCases($actual_file, $expected_file): void
    {
        $actual_target_paths = [];
        foreach (TargetPaths::fromFileName($actual_file) as $target => $paths) {
            $actual_target_paths[] = TargetPaths::serialize(strval($target), $paths);
        };

        $expected_target_paths = trim(file_get_contents($expected_file));

        $this->assertEquals(implode("\n", $actual_target_paths), $expected_target_paths);
    }
}
