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

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Exception;

use Crtl\RequestDtoResolverBundle\Exception\RequestValidationException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class RequestValidationExceptionTest extends TestCase
{
    protected ConstraintViolationListInterface&MockObject $violations;

    protected \stdClass $object;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->object = new \stdClass();
        $this->violations = $this->createMock(ConstraintViolationListInterface::class);
    }

    public function testGetMessageReturnsCorrectMessageWithClassName(): void
    {
        $exception = new RequestValidationException($this->object, $this->violations);

        $this->assertEquals('Error validating stdClass', $exception->getMessage());
    }

    public function testGetObjectReturnsTheValidatedObjectInstance(): void
    {
        $exception = new RequestValidationException($this->object, $this->violations);

        $this->assertSame($this->object, $exception->getObject());
    }

    public function testGetViolationsReturnsTheConstraintViolationListInstance(): void
    {
        $exception = new RequestValidationException($this->object, $this->violations);
        $this->assertSame($this->violations, $exception->getViolations());
    }

    public function testCreateSuccessfullyInstantiatesRequestValidationExceptionViaStaticMethod(): void
    {
        $exception = RequestValidationException::create($this->object, $this->violations);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(RequestValidationException::class, $exception);
        $this->assertSame($this->object, $exception->getObject());
        $this->assertSame($this->violations, $exception->getViolations());
        $this->assertEquals('Error validating stdClass', $exception->getMessage());
    }
}
