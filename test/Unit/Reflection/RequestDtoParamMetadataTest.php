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

use Crtl\RequestDtoResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadata;
use PHPUnit\Framework\TestCase;

final class RequestDtoParamMetadataTest extends TestCase
{
    public function testGetClassNameReturnsCorrectClassName(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $this->assertEquals(ParamMetadataDummyDto::class, $metadata->getClassName());
    }

    public function testGetPropertyNameReturnsCorrectPropertyName(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $this->assertEquals('prop', $metadata->getPropertyName());
    }

    public function testGetNestedDtoClassNameReturnsCorrectClassName(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed', ParamMetadataNestedDto::class);
        $this->assertEquals(ParamMetadataNestedDto::class, $metadata->getNestedDtoClassName());

        $metadataNull = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed', null);
        $this->assertNull($metadataNull->getNestedDtoClassName());
    }

    public function testGetReflectionClassReturnsCorrectReflectionClass(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $reflection = $metadata->getReflectionClass();
        $this->assertEquals(ParamMetadataDummyDto::class, $reflection->getName());
    }

    public function testGetReflectionPropertyReturnsCorrectReflectionProperty(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $reflection = $metadata->getReflectionProperty();
        $this->assertEquals('prop', $reflection->getName());
        $this->assertEquals(ParamMetadataDummyDto::class, $reflection->getDeclaringClass()->getName());
    }

    public function testGetAttributeReturnsAbstractParamAttribute(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $attribute = $metadata->getAttribute();
        $this->assertInstanceOf(QueryParam::class, $attribute);
        $this->assertEquals('prop', $attribute->getName());
    }

    public function testGetAttributeSetsParentAttributeWhenProvided(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $parent = new BodyParam();
        $attribute = $metadata->getAttribute($parent);

        $reflection = new \ReflectionProperty(AbstractParam::class, 'parent');
        $reflection->setAccessible(true);
        $this->assertSame($parent, $reflection->getValue($attribute));
    }

    public function testGetAttributeThrowsLogicExceptionWhenAttributeIsMissing(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'noAttributeProp', 'mixed');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Property Crtl\RequestDtoResolverBundle\Test\Unit\Reflection\ParamMetadataDummyDto::$noAttributeProp is missing an AbstractParam attribute.');
        $metadata->getAttribute();
    }

    public function testGetAttributeTriggersWarningWhenMultipleAttributesArePresent(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'multipleAttributesProp', 'mixed');

        $warningTriggered = false;
        set_error_handler(function ($errno, $errstr) use (&$warningTriggered) {
            if (E_USER_WARNING === $errno && str_contains($errstr, 'has more than one AbstractParam attribute')) {
                $warningTriggered = true;

                return true;
            }

            return false;
        });

        $attribute = $metadata->getAttribute();
        restore_error_handler();

        $this->assertTrue($warningTriggered);
        $this->assertInstanceOf(QueryParam::class, $attribute);
    }

    public function testSetValueSetsPropertyValueOnDto(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        $dto = new ParamMetadataDummyDto();
        $metadata->setValue($dto, 'new value');
        $this->assertEquals('new value', $dto->prop);
    }

    public function testIsNullable(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed');
        self::assertFalse($metadata->isNullable());

        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', 'mixed', isNullable: true);
        self::assertTrue($metadata->isNullable());
    }

    public function testSerializeAndUnserializeWorksCorrectly(): void
    {
        $metadata = new RequestDtoParamMetadata(
            ParamMetadataDummyDto::class,
            'prop',
            'string',
            ParamMetadataNestedDto::class,
            true,
            true,
        );

        $serialized = serialize($metadata);
        /** @var RequestDtoParamMetadata $unserialized */
        $unserialized = unserialize($serialized);

        $this->assertEquals($metadata->getClassName(), $unserialized->getClassName());
        $this->assertEquals($metadata->getPropertyName(), $unserialized->getPropertyName());
        $this->assertEquals($metadata->getBuiltinType(), $unserialized->getBuiltinType());
        $this->assertEquals($metadata->getNestedDtoClassName(), $unserialized->getNestedDtoClassName());
        $this->assertEquals($metadata->isNestedDtoArray(), $unserialized->isNestedDtoArray());
        $this->assertEquals($metadata->isNullable(), $unserialized->isNullable());
    }

    public function testHasDefaultValueReturnsTrueWhenPropertyHasDefaultValue(): void
    {
        $metadata = new RequestDtoParamMetadata(
            ParamMetadataDummyDto::class,
            'withDefaultValue',
            'string',
            null,
            false,
            false,
        );

        self::assertTrue($metadata->hasDefaultValue());

        $metadata = new RequestDtoParamMetadata(
            ParamMetadataDummyDto::class,
            'prop',
            'string',
            null,
            false,
            false,
        );
        self::assertFalse($metadata->hasDefaultValue());
    }

    public function testGetDefaultValueReturnsDefaultValue(): void
    {
        $metadata = new RequestDtoParamMetadata(
            ParamMetadataDummyDto::class,
            'withDefaultValue',
            'string',
            null,
            false,
            false,
        );

        self::assertSame('string', $metadata->getDefaultValue());

        $metadata = new RequestDtoParamMetadata(
            ParamMetadataDummyDto::class,
            'prop',
            'string',
            null,
            false,
            false,
        );
        self::assertNull($metadata->getDefaultValue());
    }
}

final class ParamMetadataDummyDto
{
    #[QueryParam]
    public string $prop;

    public string $noAttributeProp;

    #[QueryParam]
    #[BodyParam]
    public string $multipleAttributesProp;

    public string $withDefaultValue = 'string';
}

final class ParamMetadataNestedDto
{
}
