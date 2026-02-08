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

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Traits to help implement caching.
 */
trait WithCacheTrait
{
    private ?CacheItemPoolInterface $cache = null;

    /**
     * @var array<string, CacheItemInterface|null>
     */
    private array $cacheItems = [];

    private function normalizeClassName(string $className): string
    {
        return str_replace('\\', '_', $className);
    }

    /**
     * @internal
     */
    public function getCacheKey(string $className): string
    {
        return $this->normalizeClassName($className).'_'.$this->normalizeClassName(static::class);
    }

    /**
     * @param class-string $className
     */
    private function getCacheItem(string $className): ?CacheItemInterface
    {
        if (null === $this->cache) {
            return null;
        }
        if (!array_key_exists($className, $this->cacheItems)) {
            $cacheKey = $this->getCacheKey($className);
            try {
                $cacheItem = $this->cache->getItem($cacheKey);
            } catch (InvalidArgumentException) {
                $cacheItem = null;
            }
            $this->cacheItems[$className] = $cacheItem;
        }

        return $this->cacheItems[$className];
    }

    /**
     * @param class-string $className
     */
    private function getCachedValue(string $className): ?object
    {
        try {
            $cacheItem = $this->getCacheItem($className);
        } catch (InvalidArgumentException) {
            return null;
        }

        if ($cacheItem?->isHit()) {
            return $cacheItem->get();
        }

        return null;
    }

    /**
     * @param class-string $className
     *
     * @throws InvalidArgumentException
     */
    private function cacheValue(string $className, object $metadata): void
    {
        $this->getCacheItem($className)?->set($metadata);
    }
}
