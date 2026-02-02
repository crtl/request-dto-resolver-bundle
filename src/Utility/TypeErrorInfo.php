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

namespace Crtl\RequestDtoResolverBundle\Utility;

/**
 * Wraps {@link \TypeError} and provides information about the affected property and types.
 */
final class TypeErrorInfo
{
    /**
     * Type error thrown when property value mismatches declared type.
     */
    public const ERROR_TYPE_PROPERTY = 1;

    /**
     * Type error thrown when argument mismatches declared type.
     */
    public const ERROR_TYPE_ARGUMENT = 2;

    /**
     * Type error thrown when return value misatches declared type.
     */
    public const ERROR_TYPE_RETURN = 3;

    /**
     * @var int The type of error. One of ERROR_TYPE_* constants
     */
    public readonly int $errorType;

    /**
     * @var string The expected type
     */
    public readonly string $expectedType;

    /**
     * @var string The actual given type
     */
    public readonly string $actualType;

    /**
     * @var string The affected function or property name
     *
     * @example A::$a
     * @example A::methodArg()
     * @example fn()
     */
    public readonly string $property;

    /**
     * The name of the affected argument.
     *
     * This is only set for {@link ERROR_TYPE_ARGUMENT} errors.
     */
    public readonly ?string $argumentName;

    public function __construct(
        public readonly \TypeError $error,
    ) {
        $message = $this->error->getMessage();

        $argumentName = null;
        $errorType = self::getErrorTypeFromTypeError($this->error);

        if (self::ERROR_TYPE_PROPERTY === $errorType) {
            preg_match(
                '/Cannot assign (\w+) to property ([^ ]+) of type (\w+)/',
                $message,
                $m,
            );
            assert(4 === count($m));

            $this->actualType = $m[1]; // string
            $this->property = $m[2]; // A::$a
            $this->expectedType = $m[3]; // int
        } elseif (self::ERROR_TYPE_RETURN === $errorType) {
            // testReturn(): Return value must be of type int, string returned
            // A::methodReturn(): Return value must be of type int, string returned
            preg_match(
                '/((([\w\\\]+|class@anonymous)?(::)?\w+)\(\)): Return value must be of type (\w+), (\w+) returned/',
                $message,
                $m,
            );
            assert(7 === count($m));

            $this->property = $m[2];
            $this->actualType = $m[5];
            $this->expectedType = $m[6];
        } elseif (self::ERROR_TYPE_ARGUMENT === $errorType) {
            // testFn(): Argument #1 ($a) must be of type int, string given, called in /home/glot/main.php on line 28
            // A::methodArg(): Argument #1 ($arg) must be of type int, string given, called in /home/glot/main.php on line 53
            preg_match(
                '/((([\w\\\]+|class@anonymous)?(::)?\w+)\(\)): Argument #(\d+) \((\$\w+)\) must be of type (\w+), (\w+) given/',
                $message,
                $m,
            );

            $argumentName = $m[6];
            $this->property = $m[2] ?: ($m[3] ?: $m[1]);
            $this->actualType = $m[7];
            $this->expectedType = $m[8];
        } else {
            throw new \InvalidArgumentException('Unknown error type: '.$message);
        }

        $this->errorType = $errorType;
        $this->argumentName = $argumentName;
    }

    /**
     * Returns the error type for given type error or null if not supported.
     *
     * @return int|null Error type or null if not supported type error
     */
    public static function getErrorTypeFromTypeError(\TypeError $error): ?int
    {
        $message = $error->getMessage();
        if (str_starts_with($message, 'Cannot assign ')) {
            return self::ERROR_TYPE_PROPERTY;
        } elseif (strstr($message, 'Return value must be of type ')) {
            return self::ERROR_TYPE_RETURN;
        } elseif (strstr($message, 'Argument #')) {
            return self::ERROR_TYPE_ARGUMENT;
        }

        return null;
    }
}
