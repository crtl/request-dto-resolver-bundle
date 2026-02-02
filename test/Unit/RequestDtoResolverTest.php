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

/** @noinspection PhpClassCantBeUsedAsAttributeInspection */
/** @noinspection PhpClassCantBeUsedAsAttributeInspection */
/** @noinspection PhpClassCantBeUsedAsAttributeInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Crtl\RequestDtoResolverBundle\Test\Unit;

use Crtl\RequestDtoResolverBundle\Attribute;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadata;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDtoResolverBundle\RequestDtoResolver;
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[Attribute\RequestDto]
final class UnitRequestDTO
{
}

final class RequestDtoResolverTest extends TestCase
{
    private RequestDtoResolver $resolver;

    private DtoInstanceBagInterface&MockObject $bag;

    private DtoReflectionHelper&MockObject $reflectionHelper;
    private RequestDtoMetadataFactory&MockObject $metadataFactory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->bag = $this->createMock(DtoInstanceBagInterface::class);
        $this->reflectionHelper = $this->createMock(DtoReflectionHelper::class);
        $this->metadataFactory = $this->createMock(RequestDtoMetadataFactory::class);
        $this->resolver = new RequestDtoResolver($this->bag, $this->metadataFactory, $this->reflectionHelper);
    }

    public function testResolveReturnsEmptyArrayIfArgumentTypeIsNotRequestDto(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('test', 'string', false, false, null);

        $this->reflectionHelper->expects($this->once())
            ->method('isRequestDto')
            ->with('string')
            ->willReturn(false);

        $result = $this->resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testResolveThrowsRuntimeExceptionIfDTOInstantiationFails(): void
    {
        $metadataMock = $this->createMock(RequestDtoMetadata::class);
        $metadataMock->expects(self::once())
            ->method('newInstance')->willReturn(null);

        $this->metadataFactory->expects(self::once())
            ->method('getMetadataFor')->willReturn($metadataMock);

        $this->reflectionHelper->expects($this->once())
            ->method('isRequestDto')
            ->with(UnitRequestDTO::class)
            ->willReturn(true);

        $argument = new ArgumentMetadata('test', UnitRequestDTO::class, false, false, null);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to instantiate request dto '.UnitRequestDTO::class);
        $this->resolver->resolve(new Request(), $argument);
    }

    public function testResolveReturnsArrayWithDTOAndRegistersItInDtoInstanceBag(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('test', UnitRequestDTO::class, false, false, null);

        $this->reflectionHelper->expects($this->once())
            ->method('isRequestDto')
            ->with(UnitRequestDTO::class)
            ->willReturn(true);

        $metadata = $this->createMock(RequestDtoMetadata::class);
        $metadata->expects($this->once())
            ->method('newInstance')
            ->with($request)
            ->willReturn(new UnitRequestDTO());

        $this->metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(UnitRequestDTO::class)
            ->willReturn($metadata);

        $this->bag->expects($this->once())
            ->method('registerInstance')
            ->with($this->isInstanceOf(UnitRequestDTO::class), $request);

        $result = $this->resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(UnitRequestDTO::class, $result[0]);
    }
}
