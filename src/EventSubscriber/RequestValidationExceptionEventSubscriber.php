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

namespace Crtl\RequestDtoResolverBundle\EventSubscriber;

use Crtl\RequestDtoResolverBundle\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Default event subscriber to handle {@link RequestValidationException}.
 *
 * Returns a 400 response with validation errors in the following format:
 * ```
 * {
 *   "message": "Error validating App\\Dto\\UpdateUserDto",
 *   "errors": {
 *     "email": [
 *       {
 *         "message": "This value is not a valid email address.",
 *         "code": "bd79c0ab-ddba-46cc-a703-a7a4b08de310"
 *       }
 *     ],
 *     "age": [
 *       {
 *         "message": "This value should be positive.",
 *         "code": "778b7ae0-84d3-481a-9dec-35fdb64b1d78"
 *       }
 *     ]
 *   }
 * }
 * ```
 */
final class RequestValidationExceptionEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -32],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $exception = $event->getThrowable();
        if (!$exception instanceof RequestValidationException) {
            return;
        }

        /** @var array<string, array{message: string, code: string|null}[]> $errors */
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath] ??= [];
            $errors[$propertyPath][] = [
                'message' => $violation->getMessage(),
                'code' => $violation->getCode(),
            ];
        }

        $data = [
            'message' => $exception->getMessage(),
            'errors' => $errors,
        ];

        $response = new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
