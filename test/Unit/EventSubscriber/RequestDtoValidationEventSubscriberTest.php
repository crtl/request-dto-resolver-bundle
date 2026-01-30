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

namespace Crtl\RequestDTOResolverBundle\Test\Unit\EventSubscriber;

use Crtl\RequestDTOResolverBundle\EventSubscriber\RequestDtoValidationEventSubscriber;
use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Crtl\RequestDTOResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDTOResolverBundle\Validator\RequestDtoValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class RequestDtoValidationEventSubscriberTest extends TestCase
{
    private RequestDtoValidationEventSubscriber $subscriber;

    private RequestDtoValidator&MockObject $validatorMock;
    private DtoInstanceBagInterface&MockObject $bag;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RequestDtoValidator::class);
        $this->bag = $this->createMock(DtoInstanceBagInterface::class);
        $this->subscriber = new RequestDtoValidationEventSubscriber(
            $this->validatorMock,
            $this->bag,
        );
    }

    public function testGetSubscribedEventsReturnsExpectedEvents(): void
    {
        $subscribedEvents = $this->subscriber->getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::CONTROLLER_ARGUMENTS, $subscribedEvents);
    }

    public function testOnKernelControllerArgumentsDoesNotSkipSubRequests(): void
    {
        $request = new Request();
        $event = $this->createTestEvent(HttpKernelInterface::SUB_REQUEST, $request);

        $this->bag
            ->expects(self::once())
            ->method('getRegisteredInstances')
            ->with($request)
            ->willReturn([])
        ;

        $this->subscriber->onKernelControllerArguments($event);
    }

    public function testOnKernelControllerArgumentsThrowsRequestValidationExceptionWhenValidationFails(): void
    {
        $testDto = new \stdClass();

        $request = new Request();
        $this->bag->method('getRegisteredInstances')->with($request)->willReturn([
            \stdClass::class => $testDto,
        ]);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);

        $this->validatorMock->method('validateAndHydrate')->with($testDto)->willReturn($violations);

        $event = $this->createTestEvent(HttpKernelInterface::MAIN_REQUEST, $request);

        self::expectException(RequestValidationException::class);
        $this->subscriber->onKernelControllerArguments($event);
    }

    public function testOnKernelControllerArgumentsDoesNothingWhenDTOIsValid(): void
    {
        $testDto = new \stdClass();

        $request = new Request();
        $this->bag->method('getRegisteredInstances')->with($request)->willReturn([
            \stdClass::class => $testDto,
        ]);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations
            ->expects(self::once())
            ->method('count')
            ->willReturn(0)
        ;

        $this->validatorMock
            ->expects(self::once())
            ->method('validateAndHydrate')
            ->with($testDto)
            ->willReturn($violations)
        ;

        $event = $this->createTestEvent(HttpKernelInterface::MAIN_REQUEST, $request);

        $this->subscriber->onKernelControllerArguments($event);
    }

    private function createTestEvent(int $requestType = HttpKernelInterface::MAIN_REQUEST, ?Request $request = null): ControllerArgumentsEvent
    {
        $request ??= $this->createMock(Request::class);

        return new ControllerArgumentsEvent(
            $this->createMock(KernelInterface::class),
            function () {},
            [],
            $request,
            $requestType,
        );
    }
}
