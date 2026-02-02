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
use Crtl\RequestDtoResolverBundle\RequestDtoResolver;
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDtoResolverBundle\Validator\RequestDtoValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber which validates DTOs that have been resolved before by {@link RequestDtoResolver}.
 */
final class RequestDtoValidationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestDtoValidator $validator,
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

        foreach ($this->dtoInstanceBag->getRegisteredInstances($request) as $instance) {
            $violations = $this->validator->validateAndHydrate($instance, $request);

            if ($violations->count()) {
                throw new RequestValidationException($instance, $violations);
            }
        }
    }
}
