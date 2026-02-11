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

use Crtl\RequestDtoResolverBundle\Configuration;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadata;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadata;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadataFactory;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

final class RequestDtoMetadataFactoryTest extends TestCase
{
    private DtoReflectionHelper&MockObject $reflectionHelper;

    private RequestDtoParamMetadataFactory&MockObject $paramMetadataFactory;

    private RequestDtoMetadataFactory $factory;

    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->reflectionHelper = $this->createMock(DtoReflectionHelper::class);
        $this->paramMetadataFactory = $this->createMock(RequestDtoParamMetadataFactory::class);
        $this->configuration = new Configuration(true, false);
        $this->factory = new RequestDtoMetadataFactory($this->reflectionHelper, $this->paramMetadataFactory, $this->configuration);
    }

    public function testGetMetadataForReturnsRequestDtoMetadataForGivenClassName(): void
    {
        $className = DummyDto::class;
        $validatorMetadata = $this->createMock(ClassMetadataInterface::class);

        $prop1 = new \ReflectionProperty($className, 'prop1');
        $prop2 = new \ReflectionProperty($className, 'prop2');

        $this->reflectionHelper->expects($this->once())
            ->method('getAttributedProperties')
            ->willReturn([$prop1, $prop2]);

        $this->paramMetadataFactory->expects($this->exactly(2))
            ->method('getMetadataFor')
            ->willReturnCallback(function (\ReflectionProperty $prop) {
                return new RequestDtoParamMetadata($prop->getDeclaringClass()->getName(), $prop->getName(), 'mixed');
            });

        $metadata = $this->factory->getMetadataFor($className);

        $this->assertEquals($className, $metadata->getReflectionClass()->getName());
        $this->assertEquals(['prop1', 'prop2'], array_keys(iterator_to_array($metadata->getPropertyMetadataGenerator())));
    }

    public function testGetMetadataForReturnsCachedMetadataOnCacheHit(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cachedMetadata = $this->createMock(RequestDtoMetadata::class);

        $factory = new RequestDtoMetadataFactory($this->reflectionHelper, $this->paramMetadataFactory, $this->configuration, $cache);

        $className = DummyDto::class;
        $cacheKey = $factory->getCacheKey($className);

        $cache->expects($this->once())
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn($cachedMetadata);

        $metadata = $factory->getMetadataFor($className);

        $this->assertSame($cachedMetadata, $metadata);
    }

    public function testGetMetadataForCreatesAndCachesMetadataOnCacheMiss(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $factory = new RequestDtoMetadataFactory(
            $this->reflectionHelper,
            $this->paramMetadataFactory,
            $this->configuration,
            $cache
        );

        $className = DummyDto::class;
        $cacheKey = $factory->getCacheKey($className);

        $cache->expects($this->once())
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->reflectionHelper->expects($this->once())
            ->method('getAttributedProperties')
            ->willReturn([]);

        $cacheItem->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(RequestDtoMetadata::class));

        $metadata = $factory->getMetadataFor($className);

        $this->assertEquals($className, $metadata->getReflectionClass()->getName());
    }

    public function testGetMetadataForThrowsRuntimeExceptionWhenUnsupportedTypeIsEncountered(): void
    {
        $className = DummyDto::class;

        $prop1 = new \ReflectionProperty($className, 'prop1');

        $this->reflectionHelper->expects($this->once())
            ->method('getAttributedProperties')
            ->willReturn([$prop1]);

        $this->paramMetadataFactory
            ->expects(self::once())
            ->method('getMetadataFor')
            ->with($prop1)
            ->willThrowException(new \RuntimeException('Intersection and union types are not supported.'))
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Intersection and union types are not supported.');

        $this->factory->getMetadataFor($className);
    }

    public function testGetMetadataForThrowsLogicExceptionWhenClassIsNotInstantiable(): void
    {
        $this->expectException(\LogicException::class);
        $this->factory->getMetadataFor(PrivateConstructor::class);
    }
}

final class DummyDto
{
    public string $prop1;

    public string $prop2;
}

final class PrivateConstructor
{
    private function __construct()
    {
    }
}
