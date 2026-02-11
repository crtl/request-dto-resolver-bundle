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

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @codeCoverageIgnore
 */
class RequestDtoResolverBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('default_strict')
                    ->defaultTrue()
                    ->info('Default value for the "strict" option on RequestDto attributes when not explicitly set.')
                ->end()
                ->booleanNode('default_null')
                    ->defaultFalse()
                    ->info('Default value for the "defaultNull" option on RequestDto attributes when not explicitly set.')
                ->end()
            ->end()
        ;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $builder->getDefinition(Configuration::class)
            ->setArgument('$defaultStrict', $config['default_strict'])
            ->setArgument('$defaultNull', $config['default_null']);
    }
}
