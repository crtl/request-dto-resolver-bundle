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

namespace Crtl\RequestDTOResolverBundle\Test\Integration;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;
use Crtl\RequestDTOResolverBundle\Test\Fixtures\AllParamTypesDTO;
use Crtl\RequestDTOResolverBundle\Test\Fixtures\NestedChildDTO;
use Crtl\RequestDTOResolverBundle\Utility\DtoReflectionHelper;
use PHPUnit\Framework\TestCase;

final class DtoReflectionHelperIntegrationTest extends TestCase
{
    private DtoReflectionHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new DtoReflectionHelper();
    }

    public function testGetAttributes(): void
    {
        /** @var \ReflectionClass<object> $reflectionClass */
        $reflectionClass = new \ReflectionClass(AllParamTypesDTO::class);
        $attributes = $this->helper->getAttributes($reflectionClass, RequestDto::class);
        $this->assertCount(1, $attributes);
        $this->assertEquals(RequestDto::class, $attributes[0]->getName());

        $reflectionProperty = $reflectionClass->getProperty('body');
        $attributes = $this->helper->getAttributes($reflectionProperty, BodyParam::class);
        $this->assertCount(1, $attributes);
        $this->assertEquals(BodyParam::class, $attributes[0]->getName());
    }

    public function testGetDtoClassNameFromReflectionProperty(): void
    {
        $class = new #[RequestDto] class {
            #[BodyParam]
            public ?NestedChildDTO $nested;

            #[BodyParam]
            public ?string $notDto;
        };

        $reflectionClass = new \ReflectionClass($class);

        $property = $reflectionClass->getProperty('nested');
        $type = $this->helper->getDtoClassNameFromReflectionProperty($property);
        $this->assertNotNull($type);
        $this->assertEquals(NestedChildDTO::class, $type->getName());

        $property = $reflectionClass->getProperty('notDto');
        $type = $this->helper->getDtoClassNameFromReflectionProperty($property);
        $this->assertNull($type);
    }

    public function testGetDtoClassNameFromReflectionPropertyThrowsRuntimeExceptionForUnionAndIntersectionTypes(): void
    {
        $unionClass = new #[RequestDto]
        class {
            #[BodyParam]
            public NestedChildDTO|string $unionProperty;
        };

        $intersectionClass = new #[RequestDto]
        class {
            #[BodyParam]
            // @phpstan-ignore property.unresolvableNativeType
            public NestedChildDTO&\Stringable $intersectionProperty;
        };

        $unionReflectionClass = new \ReflectionClass($unionClass);
        $unionProperty = $unionReflectionClass->getProperty('unionProperty');

        $this->expectException(\RuntimeException::class);
        $this->helper->getDtoClassNameFromReflectionProperty($unionProperty);

        $intersectionReflectionClass = new \ReflectionClass($intersectionClass);
        $intersectionProperty = $intersectionReflectionClass->getProperty('intersectionProperty');

        $this->expectException(\RuntimeException::class);
        $this->helper->getDtoClassNameFromReflectionProperty($intersectionProperty);
    }

    public function testIsRequestDto(): void
    {
        $this->assertTrue($this->helper->isRequestDto(AllParamTypesDTO::class));
        $this->assertTrue($this->helper->isRequestDto(new AllParamTypesDTO()));
        $this->assertTrue($this->helper->isRequestDto(new \ReflectionClass(AllParamTypesDTO::class)));

        $this->assertFalse($this->helper->isRequestDto(\stdClass::class));
    }
}
