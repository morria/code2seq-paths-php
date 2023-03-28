<?php

declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Paths\Subtokens;

final class SubtokensTest extends TestCase
{
    public function testEmpty(): void
    {
        $this->assertEquals(
            Subtokens::fromString(''),
            []
        );
    }

    public function testWhitespace(): void
    {
        $this->assertEquals(
            Subtokens::fromString('foo bar'),
            ['foo', 'bar']
        );
    }

    public function testCamelCase(): void
    {
        $this->assertEquals(
            Subtokens::fromString('fooBar'),
            ['foo', 'bar']
        );
    }

    public function testKebabCase(): void
    {
        $this->assertEquals(
            Subtokens::fromString('foo-bar'),
            ['foo', 'bar']
        );
    }

    public function testSnakeCase(): void
    {
        $this->assertEquals(
            Subtokens::fromString('foo_bar'),
            ['foo', 'bar']
        );
    }

    public function testNormalize(): void
    {
        $this->assertEquals(
            Subtokens::fromString('Foo-bar    Qux_baz'),
            ['foo', 'bar', 'qux', 'baz']
        );
    }

    public function testAllCaps(): void
    {
        $this->assertEquals(
            Subtokens::fromString('FOO BAR'),
            ['foo', 'bar']
        );

        $this->assertEquals(
            Subtokens::fromString('FOO-BAR'),
            ['foo', 'bar']
        );

        $this->assertEquals(
            Subtokens::fromString('FOO_BAR'),
            ['foo', 'bar']
        );
    }
}
