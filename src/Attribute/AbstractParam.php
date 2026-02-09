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

namespace Crtl\RequestDtoResolverBundle\Attribute;

use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for request dto attributes.
 */
abstract class AbstractParam
{
    /**
     * ReflectionProperty this attribute instances belongs to.
     */
    protected ?\ReflectionProperty $property = null;

    protected ?AbstractParam $parent = null;

    /**
     * @param string|null $name Name of parameter if property name does not match parameter name
     */
    public function __construct(
        /**
         * Parameter name, defaults to property name.
         */
        private readonly ?string $name = null,
    ) {
    }

    /**
     * Sets reflection property to which this attribute belongs to.
     *
     * @return $this
     */
    public function setProperty(\ReflectionProperty $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function setParent(AbstractParam $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Returns reflection property or null if it was not set yet.
     */
    public function getProperty(): ?\ReflectionProperty
    {
        return $this->property;
    }

    /**
     * Returns parameter name of property in request, defaults to property name.
     */
    public function getName(): string
    {
        $name = $this->name ?? $this->property?->getName();
        if (null === $name) {
            throw new \LogicException('Unable to determine name for attribute '.static::class.'. Make sure to call setProperty before getValueFromRequest.');
        }

        return $name;
    }

    /**
     * Retrieves the value from the request and returns it or null if no values was found.
     */
    abstract public function getValueFromRequest(Request $request): mixed;

    /**
     * Whether or not the request includes the value.
     */
    abstract public function hasValueInRequest(Request $request): bool;
}
