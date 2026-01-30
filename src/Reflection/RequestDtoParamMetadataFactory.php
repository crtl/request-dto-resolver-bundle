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

namespace Crtl\RequestDTOResolverBundle\Reflection;

use Crtl\RequestDTOResolverBundle\Utility\DtoReflectionHelper;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoParamMetadataFactory
{
    public function __construct(
        private ValidatorInterface $validator,
        private DtoReflectionHelper $reflectionHelper,
    ) {
    }

    public function getMetadataFor(\ReflectionProperty $property): RequestDtoParamMetadata
    {
        $className = $property->getDeclaringClass()->getName();
        $cacheKey = $className.'.'.$property->getName();

        //        if ($cacheItem = $this->getCachedValue($cacheKey)) {
        //            return $cacheItem;
        //        }

        /** @var ClassMetadataInterface $validatorClassMetadata */
        $validatorClassMetadata = $this->validator->getMetadataFor($className);
        $constraintedProperties = $validatorClassMetadata->getConstrainedProperties();

        $propertyName = $property->getName();

        /** @var class-string|null $nestedClassName */
        $nestedClassName = $this->reflectionHelper->getDtoClassNameFromReflectionProperty($property)?->getName();

        $metadata = new RequestDtoParamMetadata(
            $property->getDeclaringClass()->getName(),
            $propertyName,
            in_array($propertyName, $constraintedProperties, true),
            $nestedClassName,
        );

        //        $this->cacheValue($cacheKey, $metadata);
        return $metadata;
    }
}
