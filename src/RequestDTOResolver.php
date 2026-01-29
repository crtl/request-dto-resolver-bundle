<?php

namespace Crtl\RequestDTOResolverBundle;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractNestedParam;
use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDTO;
use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Value resolver that creates and validates objects annotated with the #[RequestDTO] attribute.
 *
 * This resolver is responsible for instantiating entities that are annotated with the #[RequestDTO] attribute,
 * and then validating them using Symfony's validation component.
 */
class RequestDTOResolver implements ValueResolverInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const DTO_INSTANCES_ATTRIBUTE_KEY = '_request_dto_instances';

    public function __construct(protected ValidatorInterface $validator)
    {
    }

    /**
     * Creates class for arguments if supported, validates it and returns it. If validation fails an exception is thrown.
     *
     * @return iterable<object>
     *
     * @throws RequestValidationException
     * @throws \ReflectionException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        // Return if no type was hinted
        if (!$type) {
            return [];
        }

        $reflection = $this->createRequestDtoReflection($type);

        // Return if hinted class does not have Request attribute
        if (!$reflection) {
            return [];
        }

        $object = $this->createObject($request, $reflection);

        if (!$object) {
            return [];
        }

        /** @var array<class-string, object> $currentAttributes */
        $currentAttributes = $request->attributes->get(self::DTO_INSTANCES_ATTRIBUTE_KEY, []);

        // Store instance in request attributes to be validated later on kernel.controller_arguments event
        $request->attributes->set(self::DTO_INSTANCES_ATTRIBUTE_KEY, array_merge(
            $currentAttributes,
            [
                get_class($object) => $object,
            ]
        ));

        return [$object];
    }

    /**
     * @template T of object
     *
     * @param Request             $request         The request object
     * @param \ReflectionClass<T> $reflection      Reflection class to create instance of
     * @param AbstractParam|null  $parentAttribute Optional parent attribute passed when creating nested objects
     *
     * @return T|null
     *
     * @throws \ReflectionException
     */
    protected function createObject(Request $request, \ReflectionClass $reflection, ?AbstractParam $parentAttribute = null): mixed
    {
        // Get all class properties
        $properties = $reflection->getProperties();

        // Create new class instance
        try {
            /** @var T|null $instance */
            $instance = $reflection->hasMethod('__construct')
                ? $reflection->newInstance($request)
                : $reflection->newInstanceWithoutConstructor();
        } catch (\ReflectionException $e) {
            $this->logger?->error(sprintf(
                'Unable to instantiate class %s: %s',
                $reflection->getName(),
                $e->getMessage()
            ));

            return null;
        }

        // Iterate reflection properties
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes(AbstractParam::class, \ReflectionAttribute::IS_INSTANCEOF);

            $dtoReflection = $this->createReflectionForRequestDtoProperty($property->getType());

            foreach ($propertyAttributes as $propertyAttribute) {
                /** @var AbstractParam $inst */
                $inst = $propertyAttribute->newInstance();
                $inst->setProperty($property);

                // Set parent attribute if it exists
                if ($parentAttribute) {
                    $inst->setParent($parentAttribute);
                }

                $value = $inst->getValueFromRequest($request);

                // Check if property is a request dto itself and check if it's attributed with BodyParam or QueryParam
                if ($dtoReflection && $inst instanceof AbstractNestedParam) {
                    if (is_array($value)) {
                        $value = $this->createObject($request, $dtoReflection, $inst);
                    } else {
                        $value = null;
                    }
                }

                // Set property value on result instance
                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * Checks if $className is a class which has {@link RequestDTO} attribute and returns reflection class for said dto or false otherwise.
     *
     * @param class-string|string $className
     *
     * @return \ReflectionClass<object>|false
     *
     * @phpstan-assert-if-true class-string $className
     */
    protected function createRequestDtoReflection(string $className): \ReflectionClass|false
    {
        if (!class_exists($className)) {
            return false;
        }

        $reflection = new \ReflectionClass($className);

        // Return if hinted class does not have Request attribute
        $attributes = $reflection->getAttributes(RequestDTO::class);

        return !empty($attributes) ? $reflection : false;
    }

    /**
     * Checks if type contains classname which has {@link RequestDTO} attribute.
     *
     * @return \ReflectionClass<object>|false
     */
    protected function createReflectionForRequestDtoProperty(?\ReflectionType $type): \ReflectionClass|false
    {
        if (!$type) {
            return false;
        }

        if ($type instanceof \ReflectionNamedType) {
            $types = [$type];
        } elseif ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            $types = $type->getTypes();
        } else {
            $types = [];
        }

        // Remove nullable flags from types
        $types = array_map(fn ($t) => ltrim($t, '?'), $types);

        foreach ($types as $type) {
            /** @var class-string|string $type */
            // Check if class is a request dto and return true if so
            if ($r = $this->createRequestDtoReflection($type)) {
                return $r;
            }
        }

        return false;
    }
}
