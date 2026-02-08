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
        ];
    }

    /**
     * @param array{
     *     className: class-string,
     *     properties: string[],
     *     constrainedProperties: string[],
     *     validatorMetadata: ClassMetadataInterface,
     *     propertyMetadata: RequestDtoParamMetadata[],
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->className = $data['className'];
        $this->propertyMetadata = $data['propertyMetadata'];
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
