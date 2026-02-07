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
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Event subscriber which validates DTOs that have been resolved before by {@link \Crtl\RequestDtoResolverBundle\RequestDtoResolver}.
 */
final class RequestDtoValidationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private DtoInstanceBagInterface $dtoInstanceBag,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
        ];
    }

    /**
     * @throws RequestValidationException
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();

        foreach ($this->dtoInstanceBag->getRegisteredInstances($request) as $className => $instance) {
            // Check for stored hydration violations first
            $hydrationViolations = $this->dtoInstanceBag->getHydrationViolations($className, $request);

            // Standard Symfony validation on fully-hydrated DTO
            $violations = $this->validator->validate($instance);

            if (null !== $hydrationViolations) {
                $violations->addAll($hydrationViolations);
            }

            if ($violations->count() > 0) {
                throw new RequestValidationException($instance, $violations);
            }
        }
    }
}
