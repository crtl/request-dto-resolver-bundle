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

use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadataFactory;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\TypeInfo\Type;

// we need a custom interface because getType is defined by @method and not mockable by default
interface TestExtractorInterface extends PropertyInfoExtractorInterface
{
    /**
     * @param mixed[] $context
     */
    public function getType(string $class, string $property, array $context = []): ?Type;
}

final class RequestDtoParamMetadataFactoryTest extends TestCase
{
    private DtoReflectionHelper&MockObject $reflectionHelper;

    private PropertyInfoExtractorInterface&MockObject $propertyInfoExtractor;

    private RequestDtoParamMetadataFactory $factory;

    protected function setUp(): void
    {
        $this->reflectionHelper = $this->createMock(DtoReflectionHelper::class);
        $this->propertyInfoExtractor = $this->createMock(TestExtractorInterface::class);
        $this->factory = new RequestDtoParamMetadataFactory(
            $this->reflectionHelper,
            $this->propertyInfoExtractor,
        );
    }

    public function testGetMetadataForBuildsMetadataForConstrainedProperty(): void
    {
        $className = ParamMetadataFactoryDummyDto::class;
        $property = new \ReflectionProperty($className, 'constrainedProp');

        $this->propertyInfoExtractor->expects($this->once())
            ->method('getType')
            ->with($className, 'constrainedProp')
            ->willReturn(Type::string());

        $this->reflectionHelper->expects($this->never())
            ->method('isRequestDto');

        $metadata = $this->factory->getMetadataFor($property);

        $this->assertSame('string', $metadata->getBuiltinType());
        $this->assertFalse($metadata->isNestedDtoArray());
        $this->assertNull($metadata->getNestedDtoClassName());
        $this->assertFalse($metadata->isNullable());
    }

    public function testGetMetadataForDetectsNestedDtoInArrayType(): void
    {
        $className = ParamMetadataFactoryDummyDto::class;
        $property = new \ReflectionProperty($className, 'nestedArray');

        $this->propertyInfoExtractor->expects($this->once())
            ->method('getType')
            ->with($className, 'nestedArray')
            ->willReturn(Type::array(Type::object(ParamMetadataFactoryNestedDto::class)));

        $this->reflectionHelper->expects($this->once())
            ->method('isRequestDto')
            ->with(ParamMetadataFactoryNestedDto::class)
            ->willReturn(true);

        $metadata = $this->factory->getMetadataFor($property);

        $this->assertSame('array', $metadata->getBuiltinType());
        $this->assertTrue($metadata->isNestedDtoArray());
        $this->assertSame(ParamMetadataFactoryNestedDto::class, $metadata->getNestedDtoClassName());
        $this->assertFalse($metadata->isNullable());
    }

    public function testGetMetadataForTriggersWarningOnMixedUnionFallback(): void
    {
        $className = ParamMetadataFactoryDummyDto::class;
        $property = new \ReflectionProperty($className, 'mixedUnion');

        $this->propertyInfoExtractor->expects($this->once())
            ->method('getType')
            ->with($className, 'mixedUnion')
            ->willThrowException(new \InvalidArgumentException('Cannot create union with "mixed" standalone type.'));

        $warningTriggered = false;
        set_error_handler(function ($errno, $errstr) use (&$warningTriggered) {
            if (E_USER_WARNING === $errno && str_contains($errstr, 'Unable to guess type for mixed union type')) {
                $warningTriggered = true;

                return true;
            }

            return false;
        });

        $metadata = $this->factory->getMetadataFor($property);
        restore_error_handler();

        $this->assertTrue($warningTriggered);
        $this->assertSame('mixed', $metadata->getBuiltinType());
        $this->assertFalse($metadata->isNestedDtoArray());
        $this->assertNull($metadata->getNestedDtoClassName());
        $this->assertTrue($metadata->isNullable());
    }
}

final class ParamMetadataFactoryDummyDto
{
    public string $constrainedProp;

    /**
     * @var mixed[]
     */
    public array $nestedArray;

    // @phpstan-ignore missingType.property
    public $mixedUnion;
}

final class ParamMetadataFactoryNestedDto
{
}
