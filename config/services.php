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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Crtl\RequestDTOResolverBundle\EventSubscriber\RequestDtoValidationEventSubscriber;
use Crtl\RequestDTOResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDTOResolverBundle\Reflection\RequestDtoParamMetadataFactory;
use Crtl\RequestDTOResolverBundle\RequestDtoResolver;
use Crtl\RequestDTOResolverBundle\Utility\DtoInstanceBag;
use Crtl\RequestDTOResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDTOResolverBundle\Utility\DtoReflectionHelper;
use Crtl\RequestDTOResolverBundle\Validator\RequestDtoValidator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire(false)
        ->autoconfigure(false);

    $services->set(DtoReflectionHelper::class)
        ->private();

    $services->set(DtoInstanceBag::class)
        ->private();

    $services->alias(DtoInstanceBagInterface::class, DtoInstanceBag::class);

    $services->set(RequestDtoParamMetadataFactory::class)
        ->args([
            '$validator' => service('validator'),
            '$reflectionHelper' => service(DtoReflectionHelper::class),
        ]);

    $services->set(RequestDtoMetadataFactory::class)
        ->args([
            '$validator' => service('validator'),
            '$reflectionHelper' => service(DtoReflectionHelper::class),
            '$requestDtoParamMetadataFactory' => service(RequestDtoParamMetadataFactory::class),
            '$cache' => service('cache.system'),
        ]);

    $services->set(RequestDtoValidator::class)
        ->args([
            '$validator' => service('validator'),
            '$metadataFactory' => service(RequestDtoMetadataFactory::class),
        ]);

    $services->set(RequestDtoResolver::class)
        ->args([
            '$dtoInstanceBag' => service(DtoInstanceBagInterface::class),
            '$factory' => service(RequestDtoMetadataFactory::class),
            '$reflectionHelper' => service(DtoReflectionHelper::class),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 50]);

    $services->set(RequestDtoValidationEventSubscriber::class)
        ->args([
            '$validator' => service(RequestDtoValidator::class),
            '$dtoInstanceBag' => service(DtoInstanceBagInterface::class),
        ])
        ->tag('kernel.event_subscriber');
};
