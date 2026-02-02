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

namespace Crtl\RequestDtoResolverBundle\Validator;

use Crtl\RequestDtoResolverBundle\Attribute\AbstractNestedParam;
use Crtl\RequestDtoResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadata;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadataFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Custom validator for request DTOs.
 *
 * Validation differs as such as property constraints are validated first, before properties are assigned, to ensure type safety.
 * Followed by validation of class constraints.
 */
class RequestDtoValidator
{
    public const TYPE_DEFAULTS = [
        'int' => 0,
        'string' => '',
        'float' => 0.0,
        'bool' => false,
        'array' => [],
        'object' => null,
        'null' => null,
        'mixed' => null,
        'iterable' => [],
    ];

    public function __construct(
        private ValidatorInterface $validator,
        private RequestDtoMetadataFactory $metadataFactory,
        private ?GroupSequenceExtractor $groupSequenceExtractor = null,
    ) {
    }

    /**
     * @param array<string|string[]|GroupSequence>|null $groups
     */
    private function validateAndHydrateRecursive(
        object $dto,
        Request $request,
        ?array $groups = null,
        ?AbstractParam $parent = null,
        ?RequestDtoMetadata $dtoMetadata = null
    ): ConstraintViolationListInterface {
        $className = get_class($dto);
        $dtoMetadata ??= $this->metadataFactory->getMetadataFor($className);

        if (empty($groups)) {
            $groups = [Constraint::DEFAULT_GROUP, $className];
        }

        $groupSequence = $dtoMetadata->getGroupSequence() ?? $groups;

        //        if (empty($groupSequence)) {
        //            $groupSequence = [[Constraint::DEFAULT_GROUP, $className]];
        //        }

        $violations = new ConstraintViolationList();

        /** @var list<callable> $values Callables which apply values to dto, stored for hydration after validation */
        $values = [];
        $hydrated = false;

        $newGroupSequence = $this->groupSequenceExtractor->getGroupSequence($dto, $groups, $dtoMetadata->getValidatorMetadata());

        $groupSequence = $newGroupSequence instanceof GroupSequence ? $newGroupSequence->groups : $newGroupSequence;

        foreach ($groupSequence as $groupToProcess) {
            $groupViolations = new ConstraintViolationList();

            foreach ($dtoMetadata->getPropertyMetadataGenerator() as $propertyMetadata) {
                $reflectionProperty = $propertyMetadata->getReflectionProperty();
                $propertyName = $reflectionProperty->getName();

                $attr = $dtoMetadata->getAbstractParamAttributeFromProperty($reflectionProperty, $parent);

                $dtoType = $propertyMetadata->getNestedDtoClassName();
                // When the property is a nested RequestDTO we can recurse into custom validation and skip regular validator validation, because:
                // class can either be valid or invalid, of a nested dto is not valid it therefore can also not be assigned to the property.
                // therefore no other validation is required.
                $value = $attr->getValueFromRequest($request);
                if (null !== $dtoType) {
                    if (null !== $value) {
                        $isArray = $propertyMetadata->isNestedDtoArray();

                        $nestedClassName = $dtoType;
                        /** @var class-string<object> $nestedClassName */
                        $nestedMetadata = $this->metadataFactory->getMetadataFor($nestedClassName);

                        $valueArray = $isArray ? $value : [$value];

                        $propertyViolations = new ConstraintViolationList();
                        $resultArray = [];
                        foreach ($valueArray as $i => $nestedValue) {
                            $instance = $nestedMetadata->newInstance($request);

                            $nestedAttr = clone $attr;
                            if ($isArray && $nestedAttr instanceof AbstractNestedParam) {
                                $nestedAttr->setIndex($i);
                            }

                            $nestedViolations = $this->validateAndHydrateRecursive(
                                $instance,
                                $request,
                                is_array($groupToProcess) ? $groupToProcess : [$groupToProcess],
                                $nestedAttr,
                                $nestedMetadata,
                            );

                            // Reset invalid object
                            if ($nestedViolations->count() > 0) {
                                $propertyViolations = $this->prefixViolations(
                                    $propertyViolations,
                                    // Append index to prefix only when in array mode
                                    $isArray ? ($propertyName.".$i") : $propertyName,
                                );
                                $instance = null;
                            }

                            $resultArray[] = $instance;
                            $propertyViolations->addAll($nestedViolations);
                        }

                        // Reset invalid object
                        if ($propertyViolations->count() > 0) {
                            $value = null;
                        } else {
                            $value = $isArray ? $resultArray : $resultArray[0];
                        }
                    } else {
                        $propertyViolations = new ConstraintViolationList();
                    }
                } else {
                    // only validate constrained properties
                    if ($dtoMetadata->isConstrainedProperty($reflectionProperty)) {
                        $propertyViolations = $this->validator->validatePropertyValue(
                            $className,
                            $propertyName,
                            $value,
                            $groupToProcess,
                        );
                        $propertyViolations = $this->prefixViolations($propertyViolations, $propertyName);
                    } else {
                        $propertyViolations = new ConstraintViolationList();
                    }
                }

                $groupViolations->addAll(
                    $propertyViolations,
                );

                // Set property after validation
                if (0 === $propertyViolations->count()) {
                    // Store callable to apply later when validation completed successfully
                    $values[] = fn () => $reflectionProperty->setValue($dto, $value);
                }
            }

            // 2. Short-circuit: If any property failed in this group, stop everything
            if ($groupViolations->count() > 0) {
                return $groupViolations;
            }

            // After the first sequence passed validation successfully we assume data is valid and hydrate the object
            if (false === $hydrated) {
                $hydrated = true;
                array_walk($values, fn (callable $value) => $value());
            }

            $classViolations = $this->validator
                ->validate($dto, $dtoMetadata->getClassConstraints(), $groupToProcess);

            if ($classViolations->count() > 0) {
                return $classViolations;
            }
        }

        return $violations;
    }

    /**
     * @param object                                    $dto
     * @param array<string|string[]|GroupSequence>|null $groups
     */
    public function validateAndHydrate($dto, Request $request, ?array $groups = null): ConstraintViolationListInterface
    {
        return $this->validateAndHydrateRecursive($dto, $request, $groups);
    }

    /**
     * Prefixes property paths of constraint violations with parent property name.
     */
    private function prefixViolations(ConstraintViolationListInterface $violations, string $prefix): ConstraintViolationList
    {
        $prefixedList = new ConstraintViolationList();
        foreach ($violations as $violation) {
            $path = $prefix.($violation->getPropertyPath() ? '.'.$violation->getPropertyPath() : '');

            $prefixedList->add(new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $path,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode(),
                $violation->getConstraint(),
                $violation->getCause(),
            ));
        }

        return $prefixedList;
    }
}
