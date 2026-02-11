<?php

namespace Crtl\RequestDtoResolverBundle;

/**
 * Service to access bundle configuration values
 */
final class Configuration
{

    public function __construct(
        private readonly bool $defaultStrict,
        private readonly bool $defaultNull,
    )
    {
    }

    public function getDefaultStrict(): bool
    {
        return $this->defaultStrict;
    }

    public function getDefaultNull(): bool
    {
        return $this->defaultNull;
    }

}