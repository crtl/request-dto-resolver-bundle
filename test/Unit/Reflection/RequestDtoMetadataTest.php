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

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Reflection;

use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Reflection\RequestDtoMetadata;
use Crtl\RequestDTOResolverBundle\Reflection\RequestDtoParamMetadata;
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
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', false),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', false),
            ],
            $validatorMetadata,
        );

        $this->assertCount(2, iterator_to_array($metadata->getPropertyMetadataGenerator()));
        $this->assertSame($validatorMetadata, $metadata->getValidatorMetadata());
        $this->assertEquals(DummyRequestDto::class, $metadata->getReflectionClass()->getName());
    }

    public function testIsConstrainedPropertyReturnsTrueIfPropertyHasConstraints(): void
    {
        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', true),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', false),
            ],
            $this->createMock(ClassMetadataInterface::class),
        );

        $this->assertTrue($metadata->isConstrainedProperty('prop1'));
        $this->assertTrue($metadata->isConstrainedProperty($metadata->getPropertyMetadata('prop1')->getReflectionProperty()));

        $this->assertFalse($metadata->isConstrainedProperty('prop2'));
        $this->assertFalse($metadata->isConstrainedProperty($metadata->getPropertyMetadata('prop2')->getReflectionProperty()));

        $this->assertFalse($metadata->isConstrainedProperty('nonExistent'));
    }

    public function testGetGroupSequenceReturnsArrayIfItIsSetAsArrayInValidatorMetadata(): void
    {
        $validatorMetadata = $this->createMock(ClassMetadataInterface::class);
        $validatorMetadata->method('getGroupSequence')->willReturn(null);

        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [],
            $validatorMetadata,
        );

        $this->assertNull($metadata->getGroupSequence());
    }

    public function testGetGroupSequenceReturnsArrayFromGroupSequenceObjectInValidatorMetadata(): void
    {
        $groupSequence = new GroupSequence(['Group1', 'Group2']);
        $validatorMetadata = $this->createMock(ClassMetadataInterface::class);
        $validatorMetadata->method('getGroupSequence')->willReturn($groupSequence);

        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [],
            $validatorMetadata,
        );

        $this->assertEquals(['Group1', 'Group2'], $metadata->getGroupSequence());
    }

    public function testGetGroupSequenceReturnsNullIfNoSequenceIsDefinedInValidatorMetadata(): void
    {
        $validatorMetadata = $this->createMock(ClassMetadataInterface::class);
        $validatorMetadata->method('getGroupSequence')->willReturn(null);

        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [],
            $validatorMetadata,
        );

        $this->assertNull($metadata->getGroupSequence());
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
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', true),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', false),
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
        $this->assertTrue($unserialized->isConstrainedProperty('prop1'));
        $this->assertEquals($validatorMetadata->getClassName(), $unserialized->getValidatorMetadata()->getClassName());
    }

    public function testGetAbstractParamAttributeFromPropertyReturnsNullIfNoAttributeIsFoundOnProperty(): void
    {
        $metadata = new RequestDtoMetadata(
            DummyRequestDto::class,
            [
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop1', true),
                new RequestDtoParamMetadata(DummyRequestDto::class, 'prop2', false),
            ],
            $this->createMock(ClassMetadataInterface::class),
        );

        $property = $metadata->getPropertyMetadata('prop1')->getReflectionProperty();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Property Crtl\RequestDTOResolverBundle\Test\Unit\Reflection\DummyRequestDto::$prop1 is missing an AbstractParam attribute.');

        $metadata->getAbstractParamAttributeFromProperty($property);
    }

    public function testGetAbstractParamAttributeFromPropertyTriggersWarningWhenMultipleAttributesAreFoundOnProperty(): void
    {
        $metadata = new RequestDtoMetadata(
            DtoWithMultipleAttributes::class,
            [
                new RequestDtoParamMetadata(DtoWithMultipleAttributes::class, 'prop', false),
            ],
            $this->createMock(ClassMetadataInterface::class),
        );

        $property = $metadata->getPropertyMetadata('prop')->getReflectionProperty();

        set_error_handler(function ($errno, $errstr) {
            $this->assertEquals(E_USER_WARNING, $errno);
            $this->assertStringContainsString('Property Crtl\RequestDTOResolverBundle\Test\Unit\Reflection\DtoWithMultipleAttributes::$prop has more than one AbstractParam attribute', $errstr);

            return true;
        }, E_USER_WARNING);

        $attribute = $metadata->getAbstractParamAttributeFromProperty($property);

        restore_error_handler();

        $this->assertInstanceOf(QueryParam::class, $attribute);
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
