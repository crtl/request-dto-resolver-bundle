<?php

namespace Crtl\RequestDTOResolverBundle\Test\Unit\EventSubscriber;

use Crtl\RequestDTOResolverBundle\EventSubscriber\RequestDtoValidationEventSubscriber;
use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Crtl\RequestDTOResolverBundle\RequestDTOResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestDtoValidationEventSubscriberTest extends TestCase
{
    private RequestDtoValidationEventSubscriber $subscriber;

    private ValidatorInterface&MockObject $validatorMock;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->subscriber = new RequestDtoValidationEventSubscriber(
            $this->validatorMock,
        );
    }

    public function testSubscribesToCONTROLLERARGUMENTSEvent(): void
    {
        $subscribedEvents = $this->subscriber->getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::CONTROLLER_ARGUMENTS, $subscribedEvents);
    }

    public function testOnControllerArgumentsSkipsSubRequest(): void
    {
        $event = $this->createTestEvent(HttpKernelInterface::SUB_REQUEST);

        $this->subscriber->onKernelControllerArguments($event);
        self::expectNotToPerformAssertions();
    }

    public function testOnControllerArgumentsValidatesPreviouslyResolvedDTOAndThrowsRequestValidationExceptionOnFailure(): void
    {
        $testDto = new \stdClass();

        $request = new Request(attributes: [
            RequestDTOResolver::DTO_INSTANCES_ATTRIBUTE_KEY => [
                \stdClass::class => $testDto,
            ],
        ]);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->method('count')->willReturn(1);

        $this->validatorMock->method('validate')->with($testDto)->willReturn($violations);

        $event = $this->createTestEvent(HttpKernelInterface::MAIN_REQUEST, $request);

        self::expectException(RequestValidationException::class);
        $this->subscriber->onKernelControllerArguments($event);
    }

    public function testOnControllerArgumentsDoesNothingWhenDtoIsValid(): void
    {
        $testDto = new \stdClass();

        $request = new Request(attributes: [
            RequestDTOResolver::DTO_INSTANCES_ATTRIBUTE_KEY => [
                \stdClass::class => $testDto,
            ],
        ]);

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations
            ->expects(self::once())
            ->method('count')
            ->willReturn(0)
        ;

        $this->validatorMock
            ->expects(self::once())
            ->method('validate')
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
