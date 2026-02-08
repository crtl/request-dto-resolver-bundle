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

use Crtl\RequestDtoResolverBundle\Attribute\AbstractParam;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;

class RequestDtoMetadata
{
    /**
     * @var array<string, RequestDtoParamMetadata>
     */
    private array $propertyMetadata = [];

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

        /**
         * Property names mapped to metadata
         *
         * @var RequestDtoParamMetadata[]
         */
        array $propertyMetadata,

        /**
         * Validator metadata of the Request DTO.
         *
         * @var ClassMetadataInterface
         */
        private readonly ClassMetadataInterface $validatorMetadata,
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
            'validatorMetadata' => $this->validatorMetadata,
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
        $this->validatorMetadata = $data['validatorMetadata'];
        $this->propertyMetadata = $data['propertyMetadata'];

        foreach ($this->propertyMetadata as $metadata) {
            if ($metadata->isConstrained()) {
                $this->constrainedProperties[$metadata->getPropertyName()] = $metadata;
            }
        }
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
     */
    public function getReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass($this->className);
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
