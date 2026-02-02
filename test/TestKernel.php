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

namespace Crtl\RequestDtoResolverBundle\Test;

use Crtl\RequestDtoResolverBundle\RequestDtoResolverBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new RequestDtoResolverBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function ($container) {
            $container->loadFromExtension('framework', [
                'test' => true,
                'secret' => 'test',
                'validation' => ['enabled' => true],
            ]);
        });
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../var/cache/'.$this->environment.'/'.uniqid();
    }

    public function getLogDir(): string
    {
        return __DIR__.'/../var/log/'.$this->environment;
    }

    public function getProjectDir(): string
    {
        return __DIR__.'/../';
    }
}
