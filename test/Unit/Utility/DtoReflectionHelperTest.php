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

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\AllParamTypesDTO;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\NestedChildDTO;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use PHPUnit\Framework\TestCase;

final class DtoReflectionHelperTest extends TestCase
{
    private DtoReflectionHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new DtoReflectionHelper();
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

    public function testGetAttributedProperties(): void
    {
        $class = new class {
            #[BodyParam]
            public string $someParam;

            #[FileParam]
            public mixed $file;
            #[HeaderParam]
            public string $contentType;

            #[QueryParam]
            public int $age;

            #[RouteParam]
            public string $id;

            public mixed $noAttribute;
            public mixed $noAttribute2;
        };

        $properties = $this->helper->getAttributedProperties(new \ReflectionClass($class));
        $names = array_map(fn (\ReflectionProperty $property) => $property->getName(), $properties);
        self::assertCount(5, $properties);
        self::assertContains('someParam', $names);
        self::assertContains('file', $names);
        self::assertContains('contentType', $names);
        self::assertContains('age', $names);
        self::assertContains('id', $names);
        self::assertNotContains('noAttribute', $names);
    }

    public function testIsPropertyAttributed(): void
    {
        $class = new class {
            #[BodyParam]
            public string $attributed;

            public string $notAttributed;
        };

        $reflectionClass = new \ReflectionClass($class);
        $attributed = $reflectionClass->getProperty('attributed');
        $notAttributed = $reflectionClass->getProperty('notAttributed');

        self::assertTrue(
            $this->helper->isPropertyAttributed($attributed),
        );

        self::assertFalse(
            $this->helper->isPropertyAttributed($notAttributed),
        );
    }

    public function testIsRequestDto(): void
    {
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue($this->helper->isRequestDto(AllParamTypesDTO::class));
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue($this->helper->isRequestDto(new AllParamTypesDTO()));
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue($this->helper->isRequestDto(new \ReflectionClass(AllParamTypesDTO::class)));

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertFalse($this->helper->isRequestDto(\stdClass::class));
    }
}
