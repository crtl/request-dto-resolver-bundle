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
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoMetadataFactory
{
    use WithCacheTrait;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly DtoReflectionHelper $reflectionHelper,
        private readonly RequestDtoParamMetadataFactory $requestDtoParamMetadataFactory,
        ?CacheItemPoolInterface $cache = null,
    ) {
        $this->cache = $cache;
    }

    /**
     * @param class-string $className
     */
    public function getMetadataFor(string $className): RequestDtoMetadata
    {
        if ($cacheItem = $this->getCachedValue($className)) {
            // @phpstan-ignore return.type
            return $cacheItem;
        }

        $reflectionClass = new \ReflectionClass($className);
        $validatorMetadata = $this->validator->getMetadataFor($className);

        assert($validatorMetadata instanceof ClassMetadataInterface, 'Validator metadata for '.$className.' could not be retrieved');

        $properties = $this->reflectionHelper->getDtoParamProperties($reflectionClass);

        $propertyMetadata = [];
        foreach ($properties as $property) {
            $propertyMetadata[] = $this->requestDtoParamMetadataFactory->getMetadataFor($property);
        }

        //        // TODO: Think about a better way of liniting because this only works on the first level
        //        // due to nested metadata only being instantiated on hydration.
        //        foreach ($properties as $property) {
        //            // Ensure no union or intersection types are used for nested dto params
        //            $this->reflectionHelper->getDtoClassNameFromReflectionProperty($property);
        //        }

        //        $constrainedPropertyNames = $validatorMetadata->getConstrainedProperties();

        //        $propertyNames = array_map(fn (\ReflectionProperty $prop) => $prop->getName(), $properties);
        $metadata = new RequestDtoMetadata(
            $className,
            $propertyMetadata,
            //            $propertyNames,
            //            array_intersect($constrainedPropertyNames, $propertyNames),
            $validatorMetadata,
        );

        $this->cacheValue($className, $metadata);

        return $metadata;
    }
}
