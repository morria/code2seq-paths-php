<?php

declare(strict_types=1);

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Paths\Tokens;

final class TokensTest extends TestCase
{
    public function testEmpty(): void
    {
        $this->assertEquals(
            Tokens::fromString(''),
            []
        );
    }

    public function testWhitespace(): void
    {
        $this->assertEquals(
            Tokens::fromString('foo bar'),
            ['foo', 'bar']
        );
    }

    public function testCamelCase(): void
    {
        $this->assertEquals(
            Tokens::fromString('fooBar'),
            ['foo', 'bar']
        );
    }

    public function testKebabCase(): void
    {
        $this->assertEquals(
            Tokens::fromString('foo-bar'),
            ['foo', 'bar']
        );
    }

    public function testSnakeCase(): void
    {
        $this->assertEquals(
            Tokens::fromString('foo_bar'),
            ['foo', 'bar']
        );
    }

    public function testNormalize(): void
    {
        $this->assertEquals(
            Tokens::fromString('Foo-bar    Qux_baz'),
            ['foo', 'bar', 'qux', 'baz']
        );
    }

    public function testAllCaps(): void
    {
        $this->assertEquals(
            Tokens::fromString('FOO BAR'),
            ['foo', 'bar']
        );

        $this->assertEquals(
            Tokens::fromString('FOO-BAR'),
            ['foo', 'bar']
        );

        $this->assertEquals(
            Tokens::fromString('FOO_BAR'),
            ['foo', 'bar']
        );
    }
}
