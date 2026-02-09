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
/** @noinspection PhpUnhandledExceptionInspection */

namespace Crtl\RequestDtoResolverBundle\Test\Unit;

use Crtl\RequestDtoResolverBundle\Attribute;
use Crtl\RequestDtoResolverBundle\Factory\Exception\RequestDtoHydrationException;
use Crtl\RequestDtoResolverBundle\Factory\RequestDtoFactory;
use Crtl\RequestDtoResolverBundle\RequestDtoResolver;
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolationList;

#[Attribute\RequestDto]
final class UnitRequestDTO
{
}

final class RequestDtoResolverTest extends TestCase
{
    private RequestDtoResolver $resolver;

    private DtoInstanceBagInterface&MockObject $bag;

    private DtoReflectionHelper&MockObject $reflectionHelper;
    private RequestDtoFactory&MockObject $factory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->bag = $this->createMock(DtoInstanceBagInterface::class);
        $this->reflectionHelper = $this->createMock(DtoReflectionHelper::class);
        $this->factory = $this->createMock(RequestDtoFactory::class);
        $this->resolver = new RequestDtoResolver($this->bag, $this->reflectionHelper, $this->factory);
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

    public function testResolveReturnsArrayWithDTOAndRegistersItInDtoInstanceBag(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('test', UnitRequestDTO::class, false, false, null);

        $this->reflectionHelper->expects($this->once())
            ->method('isRequestDto')
            ->with(UnitRequestDTO::class)
            ->willReturn(true);

        $dto = new UnitRequestDTO();
        $this->factory->expects($this->once())
            ->method('fromRequest')
            ->with(UnitRequestDTO::class, $request)
            ->willReturn($dto);

        $this->bag->expects($this->once())
            ->method('registerInstance')
            ->with($this->isInstanceOf(UnitRequestDTO::class), $request);

        $result = $this->resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(UnitRequestDTO::class, $result[0]);
    }

    public function testResolveStoresHydrationViolationsWhenFactoryThrows(): void
    {
        $request = new Request();
        $argument = new ArgumentMetadata('test', UnitRequestDTO::class, false, false, null);

        $this->reflectionHelper->expects($this->once())
            ->method('isRequestDto')
            ->with(UnitRequestDTO::class)
            ->willReturn(true);

        $dto = new UnitRequestDTO();
        $violations = new ConstraintViolationList();

        $this->factory->expects($this->once())
            ->method('fromRequest')
            ->willThrowException(new RequestDtoHydrationException($dto, $violations));

        $this->bag->expects($this->once())
            ->method('registerHydrationViolations')
            ->with(UnitRequestDTO::class, $violations, $request);

        $this->bag->expects($this->once())
            ->method('registerInstance')
            ->with($dto, $request);

        $result = $this->resolver->resolve($request, $argument);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($dto, $result[0]);
    }
}
