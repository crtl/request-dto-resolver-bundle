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
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
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
     * @return RequestDtoMetadata
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function getMetadataFor(string $className): RequestDtoMetadata
    {
        if ($cacheItem = $this->getCachedValue($className)) {
            // @phpstan-ignore return.type
            return $cacheItem;
        }

        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->isInstantiable()) {
            throw new \LogicException(sprintf('DTO class "%s" must be instantiable.', $reflectionClass->getName()));
        }

        $validatorMetadata = $this->validator->getMetadataFor($className);

        assert($validatorMetadata instanceof ClassMetadataInterface, 'Validator metadata for '.$className.' could not be retrieved');

        $properties = $this->reflectionHelper->getAttributedProperties($reflectionClass);

        $propertyMetadata = [];
        foreach ($properties as $property) {
            $propertyMetadata[] = $this->requestDtoParamMetadataFactory->getMetadataFor($property);
        }

        $metadata = new RequestDtoMetadata(
            $className,
            $propertyMetadata,
            $validatorMetadata,
        );

        $this->cacheValue($className, $metadata);

        return $metadata;
    }
}
