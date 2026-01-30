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

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Reflection\RequestDtoParamMetadata;
use PHPUnit\Framework\TestCase;

final class RequestDtoParamMetadataTest extends TestCase
{
    public function testGetClassNameReturnsCorrectClassName(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $this->assertEquals(ParamMetadataDummyDto::class, $metadata->getClassName());
    }

    public function testGetPropertyNameReturnsCorrectPropertyName(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $this->assertEquals('prop', $metadata->getPropertyName());
    }

    public function testIsConstrainedReturnsCorrectValue(): void
    {
        $metadataTrue = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $this->assertTrue($metadataTrue->isConstrained());

        $metadataFalse = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', false);
        $this->assertFalse($metadataFalse->isConstrained());
    }

    public function testGetNestedDtoClassNameReturnsCorrectClassName(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true, ParamMetadataNestedDto::class);
        $this->assertEquals(ParamMetadataNestedDto::class, $metadata->getNestedDtoClassName());

        $metadataNull = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true, null);
        $this->assertNull($metadataNull->getNestedDtoClassName());
    }

    public function testGetReflectionClassReturnsCorrectReflectionClass(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $reflection = $metadata->getReflectionClass();
        $this->assertEquals(ParamMetadataDummyDto::class, $reflection->getName());
    }

    public function testGetReflectionPropertyReturnsCorrectReflectionProperty(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $reflection = $metadata->getReflectionProperty();
        $this->assertEquals('prop', $reflection->getName());
        $this->assertEquals(ParamMetadataDummyDto::class, $reflection->getDeclaringClass()->getName());
    }

    public function testGetAttributeReturnsAbstractParamAttribute(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $attribute = $metadata->getAttribute();
        $this->assertInstanceOf(QueryParam::class, $attribute);
        $this->assertEquals('prop', $attribute->getName());
    }

    public function testGetAttributeSetsParentAttributeWhenProvided(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $parent = new BodyParam();
        $attribute = $metadata->getAttribute($parent);

        $reflection = new \ReflectionProperty(AbstractParam::class, 'parent');
        $reflection->setAccessible(true);
        $this->assertSame($parent, $reflection->getValue($attribute));
    }

    public function testGetAttributeThrowsLogicExceptionWhenAttributeIsMissing(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'noAttributeProp', true);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Property Crtl\RequestDTOResolverBundle\Test\Unit\Reflection\ParamMetadataDummyDto::$noAttributeProp is missing an AbstractParam attribute.');
        $metadata->getAttribute();
    }

    public function testGetAttributeTriggersWarningWhenMultipleAttributesArePresent(): void
    {
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'multipleAttributesProp', true);

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
        $metadata = new RequestDtoParamMetadata(ParamMetadataDummyDto::class, 'prop', true);
        $dto = new ParamMetadataDummyDto();
        $metadata->setValue($dto, 'new value');
        $this->assertEquals('new value', $dto->prop);
    }
}

class ParamMetadataDummyDto
{
    #[QueryParam]
    public string $prop;

    public string $noAttributeProp;

    #[QueryParam]
    #[BodyParam]
    public string $multipleAttributesProp;
}

class ParamMetadataNestedDto
{
}
