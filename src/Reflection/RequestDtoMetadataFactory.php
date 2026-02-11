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

use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Configuration;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use Psr\Cache\CacheItemPoolInterface;

class RequestDtoMetadataFactory
{
    use WithCacheTrait;

    public function __construct(
        private readonly DtoReflectionHelper $reflectionHelper,
        private readonly RequestDtoParamMetadataFactory $requestDtoParamMetadataFactory,
        private readonly Configuration $configuration,
        ?CacheItemPoolInterface $cache = null,
    ) {
        $this->cache = $cache;
    }

    /**
     * @param class-string $className
     *
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

        $classAttrs = $reflectionClass->getAttributes(RequestDto::class, \ReflectionAttribute::IS_INSTANCEOF);
        /** @var RequestDto $classAttr */
        $classAttr = count($classAttrs) > 0
            ? $classAttrs[0]->newInstance()
            : null;

        $properties = $this->reflectionHelper->getAttributedProperties($reflectionClass);

        $propertyMetadata = [];
        foreach ($properties as $property) {
            $propertyMetadata[] = $this->requestDtoParamMetadataFactory->getMetadataFor($property);
        }

        $metadata = new RequestDtoMetadata(
            $className,
            $propertyMetadata,
            strict: $classAttr->strict ?? $this->configuration->getDefaultStrict(),
            defaultNull: $classAttr->defaultNull ?? $this->configuration->getDefaultNull(),
        );

        $this->cacheValue($className, $metadata);

        return $metadata;
    }
}
