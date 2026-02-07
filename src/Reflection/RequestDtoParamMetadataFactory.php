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

namespace Crtl\RequestDtoResolverBundle\Reflection;

use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use Crtl\RequestDtoResolverBundle\Utility\TypeHelper;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoParamMetadataFactory
{
    public function __construct(
        private ValidatorInterface $validator,
        private DtoReflectionHelper $reflectionHelper,
        private readonly PropertyInfoExtractorInterface $propertyInfoExtractor,
    ) {
    }

    public function getMetadataFor(\ReflectionProperty $property): RequestDtoParamMetadata
    {
        $className = $property->getDeclaringClass()->getName();
        $cacheKey = $className.'.'.$property->getName();

        /** @var ClassMetadataInterface $validatorClassMetadata */
        $validatorClassMetadata = $this->validator->getMetadataFor($className);
        $constrainedProperties = $validatorClassMetadata->getConstrainedProperties();

        $propertyName = $property->getName();

        try {
            $type = $this->propertyInfoExtractor->getType($className, $propertyName);
        } catch (\InvalidArgumentException $e) {
            // Compatibility fix because somehow type-info does not support unions with mixed.
            if ('Cannot create union with "mixed" standalone type.' !== $e->getMessage()) {
                throw $e;
            }

            trigger_error(
                sprintf('Unable to guess type for mixed union type for %s::%s', $className, $propertyName),
                E_USER_WARNING,
            );

            // Defensive fallback for invalid PHPDoc unions involving `mixed`
            $type = Type::mixed();
        }

        $typeDescription = TypeHelper::describe($type);

        $nestedClassName = null;
        $isArrayType = false;

        if ($type instanceof CollectionType) {
            $isArrayType = true;
            $type = $type->getCollectionValueType();
        } elseif ($type instanceof UnionType) {
            // get object type from union like ?T or null|T
            foreach ($type->getTypes() as $unionType) {
                if ($unionType instanceof ObjectType) {
                    $type = $unionType;
                    break;
                }
            }
        }

        if ($type instanceof ObjectType) {
            $isDto = $this->reflectionHelper->isRequestDto($type->getClassName());

            if ($isDto) {
                $nestedClassName = $type->getClassName();
            }
        }

        /** @var class-string|null $nestedClassName make phpstan happy */
        $definetlyClassString = $nestedClassName;

        $metadata = new RequestDtoParamMetadata(
            $property->getDeclaringClass()->getName(),
            $propertyName,
            $typeDescription['builtInType'],
            in_array($propertyName, $constrainedProperties, true),
            $definetlyClassString,
            $isArrayType,
            // check if type is nullable and fall back to true when no type specified
            $property->getType()?->allowsNull() ?? true,
        );

        return $metadata;
    }
}
