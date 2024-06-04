<?php

namespace Crtl\RequestDTOResolverBundle\Attribute;

use LogicException;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class for request dto attributes
 */
abstract class AbstractParam
{

    /**
     * ReflectionProperty this attribute instances belongs to
     * @var ReflectionProperty|null
     */
    protected ?ReflectionProperty $property = null;

    protected ?AbstractParam $parent = null;

    /**
     * @param string|null $name Name of parameter if property name does not match parameter name
     */
    public function __construct(
        /**
         * Parameter name, defaults to property name
         */
        private readonly ?string $name = null,
    ) {
    }

    /**
     * Sets reflection property to which this attribute belongs to
     *
     * @param ReflectionProperty $property
     * @return $this
     */
    public function setProperty(ReflectionProperty $property): self
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
     * Returns reflection property or null if it was not set yet
     * @return ReflectionProperty|null
     */
    public function getProperty(): ?ReflectionProperty
    {
        return $this->property;
    }


    /**
     * Returns parameter name of property in request, defaults to property name
     * @return string|null
     */
    public function getName(): ?string
    {
        if (!$this->name && !$this->property) {
            throw new LogicException("Unable to determine name for attribute "
                . static::class
                . ". Make sure to call setProperty before getValueFromRequest.", );
        }
        return $this->name ?? $this->property?->getName();
    }

    /**
     * Retrieves the value from the request and returns it or null if no values was found
     *
     * @param Request $request
     * @return mixed
     */
    abstract public function getValueFromRequest(Request $request): mixed;
}
