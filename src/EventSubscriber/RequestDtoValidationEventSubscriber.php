<?php

namespace Crtl\RequestDTOResolverBundle\EventSubscriber;

use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Crtl\RequestDTOResolverBundle\RequestDTOResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Event subscriber which validates DTOs that have been resolved before by {@link RequestDTOResolver}.
 */
class RequestDtoValidationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ValidatorInterface $validator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $dtoInstances = $request->attributes->get(RequestDTOResolver::DTO_INSTANCES_ATTRIBUTE_KEY, []);

        assert(is_array($dtoInstances));

        foreach ($dtoInstances as $instance) {
            assert(is_object($instance));
            $violations = $this->validator->validate($instance);

            if ($violations->count()) {
                throw new RequestValidationException($instance, $violations);
            }
        }
    }
}
