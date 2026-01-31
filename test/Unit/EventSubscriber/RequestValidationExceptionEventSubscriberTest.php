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

use Crtl\RequestDTOResolverBundle\EventSubscriber\RequestValidationExceptionEventSubscriber;
use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

final class RequestValidationExceptionEventSubscriberTest extends TestCase
{
    private RequestValidationExceptionEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new RequestValidationExceptionEventSubscriber();
    }

    public function testGetSubscribedEventsReturnsExpectedEvents(): void
    {
        $subscribedEvents = RequestValidationExceptionEventSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::EXCEPTION, $subscribedEvents);
        self::assertEquals(['onKernelException', -1024], $subscribedEvents[KernelEvents::EXCEPTION]);
    }

    public function testOnKernelExceptionSkipsSubRequests(): void
    {
        $event = $this->createExceptionEvent(new \Exception(), HttpKernelInterface::SUB_REQUEST);

        $this->subscriber->onKernelException($event);

        self::assertNull($event->getResponse());
    }

    public function testOnKernelExceptionSkipsNonRequestValidationException(): void
    {
        $event = $this->createExceptionEvent(new \Exception());

        $this->subscriber->onKernelException($event);

        self::assertNull($event->getResponse());
    }

    public function testOnKernelExceptionSetsJsonResponseForRequestValidationException(): void
    {
        $violation1 = $this->createMockViolation('email', 'Invalid email', 'EMAIL_ERROR');

        $violation1b = $this->createMockViolation('email', 'Email too short', 'EMAIL_SHORT');

        $violation2 = $this->createMockViolation('age', 'Too young', 'AGE_ERROR');

        $violation3 = $this->createMockViolation('', 'Global error', 'GLOBAL_ERROR');

        $violations = new ConstraintViolationList([$violation1, $violation1b, $violation2, $violation3]);
        $dto = new \stdClass();
        $exception = new RequestValidationException($dto, $violations);

        $event = $this->createExceptionEvent($exception);

        $this->subscriber->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        self::assertNotFalse($response->getContent());

        $data = json_decode($response->getContent(), true);
        self::assertEquals('Error validating stdClass', $data['message']);
        self::assertArrayHasKey('errors', $data);
        self::assertCount(3, $data['errors']);

        self::assertEquals([
            'email' => [
                ['message' => 'Invalid email', 'code' => 'EMAIL_ERROR'],
                ['message' => 'Email too short', 'code' => 'EMAIL_SHORT']
            ],
            'age' => [
                ['message' => 'Too young', 'code' => 'AGE_ERROR']
            ],
            '' => [
                ['message' => 'Global error', 'code' => 'GLOBAL_ERROR']
            ]
        ], $data['errors']);
    }

    private function createExceptionEvent(\Throwable $throwable, int $requestType = HttpKernelInterface::MAIN_REQUEST): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            $requestType,
            $throwable,
        );
    }

    private function createMockViolation(string $propertyPath, string $message, string $code): ConstraintViolationInterface
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->method('getPropertyPath')->willReturn($propertyPath);
        $violation->method('getMessage')->willReturn($message);
        $violation->method('getCode')->willReturn($code);

        return $violation;
    }
}
