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

namespace Crtl\RequestDtoResolverBundle;

use Crtl\RequestDtoResolverBundle\Factory\Exception\RequestDtoHydrationException;
use Crtl\RequestDtoResolverBundle\Factory\RequestDtoFactory;
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Value resolver that creates and validates objects annotated with the #[RequestDTO] attribute.
 *
 * This resolver is responsible for instantiating entities that are annotated with the #[RequestDTO] attribute,
 * and then validating them using Symfony's validation component.
 */
readonly class RequestDtoResolver implements ValueResolverInterface
{
    public function __construct(
        private DtoInstanceBagInterface $dtoInstanceBag,
        private DtoReflectionHelper $reflectionHelper,
        private RequestDtoFactory $requestDtoFactory,
    ) {
    }

    /**
     * Creates class for arguments if supported, validates it and returns it. If validation fails an exception is thrown.
     *
     * @return iterable<object>
     *
     * @throws \ReflectionException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (null === $type || !$this->reflectionHelper->isRequestDto($type)) {
            return [];
        }

        try {
            $object = $this->requestDtoFactory->fromRequest($type, $request);
        } catch (RequestDtoHydrationException $e) {
            $object = $e->object;
            $this->dtoInstanceBag->registerHydrationViolations(get_class($object), $e->violations, $request);
        }

        $this->dtoInstanceBag->registerInstance($object, $request);

        return [$object];
    }
}
