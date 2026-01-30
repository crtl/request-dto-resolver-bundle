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

namespace Crtl\RequestDTOResolverBundle\Reflection;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;

class RequestDtoParamMetadata
{
    /**
     * @var \ReflectionClass<object>
     */
    private \ReflectionClass $reflectionClass;
    private \ReflectionProperty $reflectionProperty;

    public function __construct(
        /**
         * Class that the property belongs to.
         *
         * @var class-string
         */
        private readonly string $className,

        /**
         * Name of the property.
         *
         * @var string
         */
        private readonly string $propertyName,

        /**
         * Whether the property is constrained by validation.
         *
         * @var bool
         */
        private readonly bool $isConstrained,

        /**
         * Typehint classname of nested dto class or null if not a dto.
         *
         * @var class-string|null
         */
        private readonly ?string $nestedDtoClassName = null,
    ) {
    }

    public function isConstrained(): bool
    {
        return $this->isConstrained;
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return class-string|null
     */
    public function getNestedDtoClassName(): ?string
    {
        return $this->nestedDtoClassName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return \ReflectionClass<object>
     */
    public function getReflectionClass(): \ReflectionClass
    {
        if (!isset($this->reflectionClass)) {
            $this->reflectionClass = new \ReflectionClass($this->className);
        }

        return $this->reflectionClass;
    }

    public function getReflectionProperty(): \ReflectionProperty
    {
        if (!isset($this->reflectionProperty)) {
            $this->reflectionProperty = $this->getReflectionClass()->getProperty($this->propertyName);
        }

        return $this->reflectionProperty;
    }

    /**
     * Returns the AbstractParam attribute for this property, optionally with a parent.
     */
    public function getAttribute(?AbstractParam $parent = null): AbstractParam
    {
        $property = $this->getReflectionProperty();
        $attrs = $property->getAttributes(AbstractParam::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($attrs)) {
            throw new \LogicException(sprintf('Property %s::$%s is missing an AbstractParam attribute.', $this->className, $this->propertyName));
        }

        if (count($attrs) > 1) {
            trigger_error(
                sprintf('Property %s::$%s has more than one AbstractParam attribute. Only the first one will be used.', $this->className, $this->propertyName),
                E_USER_WARNING,
            );
        }

        /** @var AbstractParam $attribute */
        $attribute = $attrs[0]->newInstance();
        $attribute->setProperty($property);

        if ($parent) {
            $attribute->setParent($parent);
        }

        return $attribute;
    }

    public function setValue(object $dto, mixed $value): void
    {
        $this->getReflectionProperty()->setValue($dto, $value);
    }

    public function __serialize(): array
    {
        return [
            'className' => $this->className,
            'propertyName' => $this->propertyName,
            'isConstrained' => $this->isConstrained,
            'nestedDtoClassName' => $this->nestedDtoClassName,
        ];
    }

    /**
     * @param array{className: class-string, propertyName: string, isConstrained: bool, nestedDtoClassName: class-string|null} $data
     */
    public function __unserialize(array $data): void
    {
        $this->className = $data['className'];
        $this->propertyName = $data['propertyName'];
        $this->isConstrained = $data['isConstrained'];
        $this->nestedDtoClassName = $data['nestedDtoClassName'];
    }
}
