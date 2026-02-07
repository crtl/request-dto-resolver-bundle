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

use Crtl\RequestDtoResolverBundle\EventSubscriber\RequestDtoValidationEventSubscriber;
use Crtl\RequestDtoResolverBundle\EventSubscriber\RequestValidationExceptionEventSubscriber;
use Crtl\RequestDtoResolverBundle\Factory\RequestDtoFactory;
use Crtl\RequestDtoResolverBundle\PropertyInfo\PropertyInfoExtractorFactory;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadataFactory;
use Crtl\RequestDtoResolverBundle\RequestDtoResolver;
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBag;
use Crtl\RequestDtoResolverBundle\Utility\DtoInstanceBagInterface;
use Crtl\RequestDtoResolverBundle\Utility\DtoReflectionHelper;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

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

    $services->set(ReflectionExtractor::class)
        ->private();

    $services->set(PhpDocExtractor::class)
        ->private();

    $services->set(PropertyInfoExtractorFactory::class)
        ->args([
            '$reflectionExtractor' => service(ReflectionExtractor::class),
            '$phpDocExtractor' => service(PhpDocExtractor::class),
        ])
    ;

    // Bundle-specific PropertyInfo service (does NOT replace the app's global "property_info")
    $services->set('crtl_request_dto_resolver_bundle.property_extractor', PropertyInfoExtractorInterface::class)
        ->factory([
            service(PropertyInfoExtractorFactory::class),
            'create'
        ]);

    $services->set(RequestDtoParamMetadataFactory::class)
        ->args([
            '$validator' => service('validator'),
            '$reflectionHelper' => service(DtoReflectionHelper::class),
            '$propertyInfoExtractor' => service('crtl_request_dto_resolver_bundle.property_extractor'),
        ]);

    $services->set(RequestDtoMetadataFactory::class)
        ->args([
            '$validator' => service('validator'),
            '$reflectionHelper' => service(DtoReflectionHelper::class),
            '$requestDtoParamMetadataFactory' => service(RequestDtoParamMetadataFactory::class),
            '$cache' => service('cache.system'),
        ]);

    $services->set(RequestDtoFactory::class)
        ->args([
            '$metadataFactory' => service(RequestDtoMetadataFactory::class),
        ])
        ->public();

    $services->set(RequestDtoResolver::class)
        ->args([
            '$dtoInstanceBag' => service(DtoInstanceBagInterface::class),
            '$reflectionHelper' => service(DtoReflectionHelper::class),
            '$requestDtoFactory' => service(RequestDtoFactory::class),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 50]);

    $services->set(RequestDtoValidationEventSubscriber::class)
        ->args([
            '$validator' => service('validator'),
            '$dtoInstanceBag' => service(DtoInstanceBagInterface::class),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(RequestValidationExceptionEventSubscriber::class)
        ->tag('kernel.event_subscriber');
};
