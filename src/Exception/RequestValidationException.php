<?php

namespace Crtl\RequestDTOResolverBundle\Exception;

use InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationException extends InvalidArgumentException
{

    public function __construct(protected object $object, protected ConstraintViolationListInterface $violations)
    {
        parent::__construct("Error validating " . get_class($this->object));
    }

    public static function create(object $object, ConstraintViolationListInterface $violations): self
    {
        return new self($object, $violations);
    }

    /**
     * Returns the object which was validated
     *
     * @return object
     */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * Returns constraint violations
     *
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

}
