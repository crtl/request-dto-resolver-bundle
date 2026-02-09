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

namespace Crtl\RequestDtoResolverBundle\Factory\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Thrown when a request dto cannot be hydrated because of type conflicts.
 */
final class RequestDtoHydrationException extends \Exception
{
    public function __construct(
        /**
         * The className of the DTO that could not be hydrated.
         */
        public readonly object $object,
        /**
         * A list of constraint violations.
         *
         * @var ConstraintViolationListInterface
         */
        public readonly ConstraintViolationListInterface $violations,
        ?\Throwable $previous = null
    ) {
        $message = sprintf('Could not hydrate DTO %s.', get_class($this->object));
        parent::__construct($message, previous: $previous);
    }
}
