<?php

/*
 * This file is part of a private project.
 *
 * Copyright 2026 Crtl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Utility;

use Crtl\RequestDtoResolverBundle\Utility\TypeErrorInfo;
use PHPUnit\Framework\TestCase;

final class TypeErrorInfoTest extends TestCase
{
    public function testItHandlesPropertyTypeError(): void
    {
        $obj = new class {
            public int $prop;
        };

        try {
            $obj->prop = 'string'; // @phpstan-ignore-line
        } catch (\TypeError $e) {
            $info = new TypeErrorInfo($e);

            $this->assertEquals(TypeErrorInfo::ERROR_TYPE_PROPERTY, $info->errorType);
            $this->assertEquals('string', $info->actualType);
            $this->assertEquals('int', $info->expectedType);
            $this->assertStringContainsString('prop', $info->property);
        }
    }

    public function testItHandlesReturnTypeError(): void
    {
        $obj = new class {
            public function test(): int
            {
                return 'string'; // @phpstan-ignore-line
            }
        };

        try {
            $obj->test(); // @phpstan-ignore-line
        } catch (\TypeError $e) {
            $info = new TypeErrorInfo($e);

            $this->assertEquals(TypeErrorInfo::ERROR_TYPE_RETURN, $info->errorType);
            $this->assertEquals('string', $info->expectedType);
            $this->assertEquals('int', $info->actualType);
            $this->assertStringContainsString('class@anonymous', $info->property);
        }
    }

    public function testItHandlesArgumentTypeError(): void
    {
        $obj = new class {
            public function test(int $arg): void
            {
            }
        };

        try {
            $obj->test('string'); // @phpstan-ignore-line
        } catch (\TypeError $e) {
            $info = new TypeErrorInfo($e);

            $this->assertEquals(TypeErrorInfo::ERROR_TYPE_ARGUMENT, $info->errorType);
            $this->assertEquals('string', $info->expectedType);
            $this->assertEquals('int', $info->actualType);
            $this->assertEquals('$arg', $info->argumentName);
            $this->assertStringContainsString('anonymous', $info->property);
        }
    }

    public function testItThrowsExceptionOnUnknownErrorMessage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown error type: some random error');

        new TypeErrorInfo(new \TypeError('some random error'));
    }
}
