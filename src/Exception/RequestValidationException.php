<?php

namespace Crtl\RequestDTOResolverBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Exception thrown when a request dto validation fails.
 */
class RequestValidationException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param object                           $object     the object that was validated
     * @param ConstraintViolationListInterface $violations the list of constraint violations
     */
    public function __construct(protected object $object, protected ConstraintViolationListInterface $violations)
    {
        parent::__construct('Error validating '.get_class($this->object));
    }

    /**
     * Creates a new RequestValidationException.
     *
     * @param object                           $object     the object that was validated
     * @param ConstraintViolationListInterface $violations the list of constraint violations
     */
    public static function create(object $object, ConstraintViolationListInterface $violations): self
    {
        return new self($object, $violations);
    }

    /**
     * Returns the object that was validated.
     */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * Returns the list of constraint violations.
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
