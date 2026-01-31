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

namespace Crtl\RequestDTOResolverBundle\Utility;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;

/**
 * @internal
 */
class DtoReflectionHelper
{
    /**
     * Returns all reflection properties of a class which are marked with {@link AbstractParam} attribute.
     *
     * @param \ReflectionClass<object> $class
     *
     * @return \ReflectionProperty[]
     *
     * @throws \ReflectionException
     */
    public function getDtoParamProperties(\ReflectionClass $class): array
    {
        $properties = $class->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];
        foreach ($properties as $property) {
            if (!$property->isStatic() && $this->isReflectionDtoParam($property)) {
                $result[] = $property;
            }
        }

        return $result;
    }

    /**
     * Helper to get instance of attributes of a given class.
     *
     * @template T of object
     *
     * @param \ReflectionProperty|\ReflectionClass<object> $class
     * @param class-string<T>                              $attributeClass
     *
     * @return \ReflectionAttribute<T>[]
     */
    public function getAttributes(\ReflectionProperty|\ReflectionClass $class, string $attributeClass): array
    {
        return $class->getAttributes($attributeClass, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Returns the first reflection type that is a request dto or null if type is not a request dto.
     */
    public function getDtoClassNameFromReflectionProperty(\ReflectionProperty $property): ?\ReflectionNamedType
    {
        $types = $property->getType();

        $normalizedTypes = match (true) {
            $types instanceof \ReflectionNamedType => [$types],
            $types instanceof \ReflectionIntersectionType,
            $types instanceof \ReflectionUnionType => throw new \RuntimeException('Intersection and union types are not supported.'),
            default => [],
        };

        foreach ($normalizedTypes as $type) {
            if ($this->isRequestDto($type->getName())) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Checks whether given object, relfection class or class name is a request dto.
     *
     * @throws \ReflectionException
     */
    public function isRequestDto(string|object $class): bool
    {
        if (is_string($class) && (interface_exists($class) || !class_exists($class))) {
            return false;
        }

        if (!$class instanceof \ReflectionClass) {
            $class = new \ReflectionClass($class);
        }

        $attrs = $this->getAttributes($class, RequestDto::class);

        return count($attrs) > 0;
    }

    /**
     * Checks whether reflection property has {@link AbstractParam} attribute.
     */
    public function isReflectionDtoParam(\ReflectionProperty $property): bool
    {
        $attrs = $property->getAttributes(AbstractParam::class, \ReflectionAttribute::IS_INSTANCEOF);

        return count($attrs) > 0;
    }
}
