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

namespace Crtl\RequestDTOResolverBundle;

use Crtl\RequestDTOResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDTOResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDTOResolverBundle\Utility\DtoReflectionHelper;
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
        private RequestDtoMetadataFactory $factory,
        private DtoReflectionHelper $reflectionHelper,
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

        /** @var class-string<object> $type */
        $metadata = $this->factory->getMetadataFor($type);
        $object = $metadata->newInstance($request);

        if (null === $object) {
            throw new \RuntimeException('Failed to instantiate request dto '.$type);
        }

        $this->dtoInstanceBag->registerInstance($object, $request);

        return [$object];
    }
}
