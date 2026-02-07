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

use Crtl\RequestDtoResolverBundle\PropertyInfo\PropertyInfoExtractorFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->get(PropertyInfoExtractorFactory::class)
        ->public();

    $services->set(Crtl\RequestDtoResolverBundle\Test\Fixtures\GroupProvider\TestGroupProvider::class)
        ->tag('validator.group_provider')
        ->public();
};
