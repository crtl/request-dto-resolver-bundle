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

namespace Crtl\RequestDtoResolverBundle\Reflection;

use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

class RequestDtoMetadata
{
    /**
     * @var array<string, RequestDtoParamMetadata>
     */
    private array $propertyMetadata = [];

    /**
     * @var \ReflectionClass<object>|null
     */
    private ?\ReflectionClass $reflectionClass = null;

    /**
     * @param RequestDtoParamMetadata[] $propertyMetadata
     */
    public function __construct(
        /**
         * The fully qualified class name of the Request DTO.
         *
         * @var class-string
         */
        private readonly string $className,

        /*
         * Property names mapped to metadata
         *
         * @var RequestDtoParamMetadata[]
         */
        array $propertyMetadata,

        private readonly bool $strict = false,
        private readonly bool $defaultNull = false,
    ) {
        foreach ($propertyMetadata as $propMetadata) {
            $propertyName = $propMetadata->getPropertyName();
            $this->propertyMetadata[$propertyName] = $propMetadata;
        }
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function __serialize(): array
    {
        return [
            'className' => $this->className,
            'propertyMetadata' => $this->propertyMetadata,
            'strict' => $this->strict,
            'defaultNull' => $this->defaultNull,
        ];
    }

    /**
     * @param array{
     *     className: class-string,
     *     propertyMetadata: RequestDtoParamMetadata[],
     *     strict: bool,
     *     defaultNull: bool,
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->className = $data['className'];
        $this->propertyMetadata = $data['propertyMetadata'];
        $this->strict = $data['strict'];
        $this->defaultNull = $data['defaultNull'];
    }

    /**
     * Returns the metadata for a given property or null if the property does not exist.
     */
    public function getPropertyMetadata(string $propertyName): ?RequestDtoParamMetadata
    {
        return $this->propertyMetadata[$propertyName] ?? null;
    }

    /**
     * Returns reflection class of request dto.
     *
     * @return \ReflectionClass<object>
     *
     * @throws \ReflectionException
     */
    public function getReflectionClass(): \ReflectionClass
    {
        $this->reflectionClass ??= new \ReflectionClass($this->className);

        return $this->reflectionClass;
    }

    public function getPropertyMetadataGenerator(): \Generator
    {
        foreach ($this->propertyMetadata as $name => $property) {
            yield $name => $property;
        }
    }

    /**
     * @throws \ReflectionException
     * @throws \TypeError
     */
    public function assignPropertyValue(object $object, string $property, mixed $value): void
    {
        if ($this->isStrict()) {
            $object->$property = $value;
        } else {
            $reflectionClass = new \ReflectionClass($object);
            $reflectionClass->getProperty($property)
                ->setValue($object, $value);
        }
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function isDefaultNull(): bool
    {
        return $this->defaultNull;
    }

    /**
     * @param mixed ...$args Arguments passed to new instance constructor, only if implemented
     *
     * @throws \ReflectionException
     */
    public function newInstance(...$args): object
    {
        $class = $this->getReflectionClass();

        return $class->getConstructor()
            ? $class->newInstance(...$args)
            : $class->newInstanceWithoutConstructor()
        ;
    }
}
