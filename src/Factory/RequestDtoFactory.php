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

namespace Crtl\RequestDtoResolverBundle\Factory;

use Crtl\RequestDtoResolverBundle\Attribute\AbstractNestedParam;
use Crtl\RequestDtoResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Factory\Exception\CircularReferenceException;
use Crtl\RequestDtoResolverBundle\Factory\Exception\PropertyHydrationException;
use Crtl\RequestDtoResolverBundle\Factory\Exception\RequestDtoHydrationException;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadata;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoMetadataFactory;
use Crtl\RequestDtoResolverBundle\Reflection\RequestDtoParamMetadata;
use Crtl\RequestDtoResolverBundle\Utility\TypeErrorInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Factory responsible for instantiating and hydrating Request DTOs.
 *
 * This factory creates DTO instances based on metadata extracted from
 * {@see AbstractParam} attributes
 * and populates their properties from either:
 *
 *  - a {@see Request} instance, or
 *  - a plain associative array
 *
 * The factory supports:
 *  - strict typing with safe error collection
 *  - nested DTOs (including arrays of nested DTOs)
 *  - default values
 *  - transformation of query parameters
 *  - recursive validation with correctly prefixed property paths
 *
 * ⚠️ Important difference between hydration sources:
 *
 *  - {@see self::fromRequest()} uses the *resolved parameter name* defined
 *    in the attribute (e.g. `#[QueryParam(name: "age")]`).
 *
 *  - {@see self::fromArray()} always uses the *original property name*
 *    and ignores any custom attribute name.
 *
 * This distinction is intentional and allows array-based hydration
 * (e.g. tests, fixtures, programmatic usage) to stay aligned with
 * DTO property names rather than HTTP parameter mappings.
 *
 * If hydration fails due to type errors or nested validation errors,
 * a {@see RequestDtoHydrationException} is thrown containing all
 * collected {@see ConstraintViolationInterface} instances.
 */
class RequestDtoFactory
{
    public function __construct(
        private readonly RequestDtoMetadataFactory $metadataFactory,
        private readonly ?TranslatorInterface $translator = null,
    ) {
    }

    /**
     * Creates and hydrates a Request DTO from a plain associative array.
     *
     * The array keys are matched **exclusively against the DTO property names**.
     * Any custom parameter name configured in an attribute
     * (e.g. `#[QueryParam(name: "age_param")]`) is ignored.
     *
     * Example:
     *
     *  DTO:
     *  #[QueryParam(name: "age_param")]
     *  public int $age;
     *
     *  Input array:
     *  [
     *      "age" => 42, // ✅ works
     *      "age_param" => 42 // ❌ ignored
     *  ]
     *
     * @template TObject of object
     *
     * @param class-string<TObject> $className fully-qualified DTO class name
     * @param array<string, mixed>  $data      input data keyed by DTO property name
     *
     * @return TObject hydrated DTO instance
     *
     * @throws RequestDtoHydrationException on type errors or nested hydration errors
     * @throws CircularReferenceException   on circular reference during nested instantiation
     * @throws \ReflectionException         on reflection/metadata failures
     */
    public function fromArray(string $className, array $data): object
    {
        return $this->createInstanceRecursive(
            $className,
            $data,
            fn (AbstractParam $attr, RequestDtoParamMetadata $paramMetadata, array $data) => $data[$paramMetadata->getPropertyName()] ?? null,
        );
    }

    /**
     * Creates and hydrates a Request DTO from an HTTP request.
     *
     * Values are resolved using the parameter attributes attached to each
     * DTO property (e.g. {@see QueryParam}, {@see BodyParam}, {@see RouteParam}, {@see FileParam}).
     *
     * @template TObject of object
     *
     * @param class-string<TObject> $className fully-qualified DTO class name
     * @param Request               $request   HTTP request used as value source
     *
     * @return TObject hydrated DTO instance
     *
     * @throws RequestDtoHydrationException on type errors or nested hydration errors
     * @throws CircularReferenceException   on circular reference during nested instantiation
     * @throws \ReflectionException         on reflection/metadata failures
     */
    public function fromRequest(string $className, Request $request): object
    {
        return $this->createInstanceRecursive(
            $className,
            $request,
            fn (AbstractParam $attr, RequestDtoParamMetadata $paramMetadata, Request $request) => $attr->getValueFromRequest($request),
        );
    }

    /**
     * Internal helper used to either create DTO from request or array.
     *
     * @template TObject of object
     * @template TContext of (Request|array<string, mixed>)
     *
     * @param class-string<TObject>                                             $className       DTO class name to instantiate
     * @param TContext                                                          $context         data source used to resolve values
     * @param callable(AbstractParam, RequestDtoParamMetadata, TContext): mixed $valueProvider   resolves a raw value for a single property
     * @param AbstractParam|null                                                $parentAttribute parent attribute for nested hydration
     * @param RequestDtoMetadata|null                                           $metadata        pre-resolved metadata for recursion reuse
     * @param string[]                                                          $stack           DTO class stack used for circular reference detection
     *
     * @return TObject hydrated DTO instance
     *
     * @throws \ReflectionException         on reflection/metadata failures
     * @throws RequestDtoHydrationException on type errors or nested hydration errors
     * @throws CircularReferenceException   on circular reference during nested instantiation
     */
    private function createInstanceRecursive(
        string $className,
        Request|array $context,
        callable $valueProvider,
        ?AbstractParam $parentAttribute = null,
        ?RequestDtoMetadata $metadata = null,
        array $stack = [],
    ): object {
        if (in_array($className, $stack, true)) {
            throw new CircularReferenceException($className, $stack);
        }

        $stack[] = $className;

        $metadata ??= $this->metadataFactory->getMetadataFor($className);

        $newInstanceArgs = $context instanceof Request ? [$context] : [];

        /** @var TObject $object */
        $object = $metadata->newInstance(...$newInstanceArgs);

        $violations = new ConstraintViolationList();

        foreach ($metadata->getPropertyMetadataGenerator() as $propertyMetadata) {
            $propertyName = $propertyMetadata->getPropertyName();

            $nestedClassName = $propertyMetadata->getNestedDtoClassName();

            $attr = $propertyMetadata->getAttribute($parentAttribute);

            if ($attr instanceof QueryParam) {
                $builtInType = strtolower($propertyMetadata->getBuiltinType());
                if (!$attr->hasTransformType() && QueryParam::isTransformType($builtInType)) {
                    $attr->setTransformType($builtInType);
                }
            }

            $requestValue = $valueProvider($attr, $propertyMetadata, $context);

            $value = $requestValue
                ?? $propertyMetadata->getDefaultValue();

            // Property is typed with nested dto
            if (null !== $nestedClassName && null !== $value) {
                $isArray = $propertyMetadata->isNestedDtoArray();

                /** @var class-string<object> $nestedClassName */
                $nestedMetadata = $this->metadataFactory
                    ->getMetadataFor($nestedClassName);

                $valueArray = $isArray ? array_values($value) : [$value];
                $nestedViolations = new ConstraintViolationList();

                $resultArray = [];
                foreach ($valueArray as $i => $nestedValue) {
                    /** @var AbstractParam $nestedAttr */
                    $nestedAttr = clone $attr;
                    if ($isArray && $nestedAttr instanceof AbstractNestedParam) {
                        $nestedAttr->setIndex($i);
                    }

                    try {
                        $resultArray[] = $this->createInstanceRecursive(
                            $nestedClassName,
                            $context instanceof Request
                                ? $context
                                : $nestedValue,
                            $valueProvider,
                            $nestedAttr,
                            $nestedMetadata,
                            $stack,
                        );
                    } catch (RequestDtoHydrationException $e) {
                        $nestedValueViolations = $this->prefixViolations(
                            $e->violations,
                            // Append index to prefix only when in array mode
                            $isArray ? ($propertyName."[$i]") : $propertyName,
                        );

                        // Collect all nested violations first before exiting outside of loop to ensure all nested items are validated.
                        $nestedViolations->addAll($nestedValueViolations);
                    }
                }

                if ($nestedViolations->count() > 0) {
                    $violations->addAll($nestedViolations);
                    continue; // continue with next property
                }

                $value = $isArray ? $resultArray : $resultArray[0];
            }

            try {
                $this->assignProperty($object, $propertyMetadata, $value);
            } catch (PropertyHydrationException $e) {
                $violation = $this->createTypeErrorViolation(
                    $e->typeError,
                    $object,
                    $propertyMetadata,
                    $value,
                );
                $violations->add($violation);
            }
        }

        if ($violations->count() > 0) {
            throw new RequestDtoHydrationException($object, $violations);
        }

        return $object;
    }

    /**
     * Helper to assign value to property and handle any occurring {@link \TypeError} by transforming them into {@link ConstraintViolation}.
     *
     * @throws PropertyHydrationException When the property could not be assigned
     */
    private function assignProperty(
        object $object,
        RequestDtoParamMetadata $metadata,
        mixed $value,
    ): void {
        try {
            $propertyName = $metadata->getPropertyName();
            $object->$propertyName = $value;
        } catch (\TypeError $e) {
            throw new PropertyHydrationException(get_class($object), $metadata->getPropertyName(), $value, $e);
        }
    }

    /**
     * Creates custom constraint violation for type errors occuring when assigning values to types properties and the type is not matching.
     *
     * @param \TypeError              $error            The type error that was thrown
     * @param object                  $object           The object being validated
     * @param RequestDtoParamMetadata $propertyMetadata The metadata of the property being validated
     */
    private function createTypeErrorViolation(
        \TypeError $error,
        object $object,
        RequestDtoParamMetadata $propertyMetadata,
        mixed $invalidValue = null
    ): ConstraintViolation {
        $errorInfo = new TypeErrorInfo($error);

        $template = 'This value should be of type {{ expected }}, {{ given }} given.1';

        $params = [
            '{{ expected }}' => $errorInfo->expectedType,
            '{{ given }}' => $errorInfo->actualType,
        ];

        $message = $this->translator?->trans($template, $params, 'validators')
            ?? str_replace(array_keys($params), array_values($params), $template);

        return new ConstraintViolation(
            $message,
            $template,
            $params,
            $object,
            $propertyMetadata->getPropertyName(),
            $invalidValue,
        );
    }

    /**
     * Prefixes property paths of constraint violations with parent property name.
     */
    private function prefixViolations(ConstraintViolationListInterface $violations, string $prefix): ConstraintViolationList
    {
        $prefixedList = new ConstraintViolationList();
        foreach ($violations as $violation) {
            $prefixedList->add($this->prefixViolation($violation, $prefix));
        }

        return $prefixedList;
    }

    private function prefixViolation(ConstraintViolationInterface $violation, string $prefix): ConstraintViolation
    {
        $path = $prefix;
        $violationPath = $violation->getPropertyPath();

        if (!empty($violationPath) && !str_starts_with($violationPath, '[')) {
            $violationPath = '.'.$violationPath;
        }

        $path .= $violationPath;

        return new ConstraintViolation(
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
        );
    }
}
