<?php

namespace Crtl\RequestDTOResolverBundle;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Value resolver which creates objects which classes are annoteated with #[Request] attribute
 * and validates them.
 *
 */
class RequestDTOResolver implements ValueResolverInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(protected ValidatorInterface $validator)
    {}

    /**
     * Creates class for arguments if supported, validates it and returns it. If validation fails an exception is thrown
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     * @throws RequestValidationException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        // Return if no type was hinted
        if (!$type) return [];

        try {
            $reflection = new ReflectionClass($type);
        } catch (ReflectionException $e) {
            $this->logger->error("Unable to create ReflectionClass for type $type: " . $e->getMessage());
            return [];
        }

        // Return if hinted class does not have Request attribute
        $attributes = $reflection->getAttributes(Attribute\RequestDTO::class);
        if (empty($attributes)) return [];

        // Get all class properties
        $properties = $reflection->getProperties();

        // Create new class instance
        try {
            $instance = $reflection->hasMethod("__construct")
                ? $reflection->newInstance($request)
                : $reflection->newInstanceWithoutConstructor();
        } catch (ReflectionException) {
            return [];
        }

        /// Iterate properties, filter for supported properties and retrieve values from request
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes(AbstractParam::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($propertyAttributes as $propertyAttribute) {
                /** @var AbstractParam $inst */
                $inst = $propertyAttribute->newInstance();

                // Assign the properties name as default
                if (!$inst->name) {
                    $inst->name = $property->getName();
                }

                // Retrieve and assign value
                $value = $inst->getValueFromRequest($request);
                $property->setValue($instance, $value);
            }
        }



        $violations = $this->validator->validate($instance);

        if ($violations->count()) {
            throw RequestValidationException::create($instance, $violations);
        }

        return [$instance];
    }


}