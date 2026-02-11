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

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use PHPUnit\Framework\TestCase;

final class RequestDtoTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $instance = new RequestDto();
        self::expectNotToPerformAssertions();
    }

    public function testStrictIsNullByDefault(): void
    {
        $instance = new RequestDto();
        self::assertNull($instance->strict);
    }

    public function testDefaultNullIsNullByDefault(): void
    {
        $instance = new RequestDto();
        self::assertNull($instance->defaultNull);
    }

    public function testConstructorAcceptsStrictOption(): void
    {
        $instance = new RequestDto(strict: true);
        self::assertTrue($instance->strict);

        $instance = new RequestDto(strict: false);
        self::assertFalse($instance->strict);
    }

    public function testConstructorAcceptsDefaultNullOption(): void
    {
        $instance = new RequestDto(defaultNull: true);
        self::assertTrue($instance->defaultNull);

        $instance = new RequestDto(defaultNull: false);
        self::assertFalse($instance->defaultNull);
    }
}
