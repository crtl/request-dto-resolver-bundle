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
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

final class RequestDtoMetadataTest extends TestCase
{
    public function testMetadataAccessorsReturnCorrectValues(): void
    {
        $validatorMetadata = $this->createMock(ClassMetadataInterface::class);

        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', 'string', false),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', 'string', false),
            ],
            $validatorMetadata,
        );

        $this->assertCount(2, iterator_to_array($metadata->getPropertyMetadataGenerator()));
        $this->assertSame(DummyRequestDto::class, $metadata->getClassName());
        $this->assertEquals(DummyRequestDto::class, $metadata->getReflectionClass()->getName());
    }


    public function testNewInstancePassesArgumentsToDTOConstructorCorrectly(): void
    {
        $metadata = new RequestDtoMetadata(RequestDtoWithConstructor::class, [], $this->createMock(ClassMetadataInterface::class));
        $instance = $metadata->newInstance('test-param', 1, 2);
        $this->assertInstanceOf(RequestDtoWithConstructor::class, $instance);

        self::assertSame(['test-param', 1, 2], $instance->param);
    }

    public function testMetadataCanBeSerializedAndUnserializedPreservingAllProperties(): void
    {
        $validatorMetadata = new ClassMetadata(DummyRequestDto::class);
        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', 'mixed', true),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', 'mixed', false),
            ],
            $validatorMetadata,
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
