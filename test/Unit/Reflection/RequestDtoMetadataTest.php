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

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Reflection;

use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadata;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadata;
use PHPUnit\Framework\TestCase;

final class RequestDtoMetadataTest extends TestCase
{
    public function testMetadataAccessorsReturnCorrectValues(): void
    {
        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', 'string'),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', 'string'),
            ],
        );

        $this->assertCount(2, iterator_to_array($metadata->getPropertyMetadataGenerator()));
        $this->assertSame(DummyRequestDto::class, $metadata->getClassName());
        $this->assertEquals(DummyRequestDto::class, $metadata->getReflectionClass()->getName());
    }

    public function testNewInstancePassesArgumentsToDTOConstructorCorrectly(): void
    {
        $metadata = new RequestDtoMetadata(RequestDtoWithConstructor::class, []);
        $instance = $metadata->newInstance('test-param', 1, 2);
        $this->assertInstanceOf(RequestDtoWithConstructor::class, $instance);

        self::assertSame(['test-param', 1, 2], $instance->param);
    }

    public function testMetadataCanBeSerializedAndUnserializedPreservingAllProperties(): void
    {
        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', 'mixed'),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', 'mixed'),
            ],
        );

        // Access properties to populate internal state if any
        $metadata->getPropertyMetadata('prop1')->getReflectionProperty();

        $serialized = serialize($metadata);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(RequestDtoMetadata::class, $unserialized);
        $this->assertEquals($metadata->getReflectionClass()->getName(), $unserialized->getReflectionClass()->getName());
        $this->assertCount(2, iterator_to_array($unserialized->getPropertyMetadataGenerator()));
    }
}

final class DummyRequestDto
{
    public string $prop1;
    public string $prop2;
}

final class RequestDtoWithConstructor
{
    public readonly mixed $param;

    // @phpstan-ignore missingType.parameter
    public function __construct(
        ...$args
    ) {
        $this->param = $args;
    }
}

final class DtoWithMultipleAttributes
{
    #[QueryParam]
    #[FileParam]
    public string $prop;
}
