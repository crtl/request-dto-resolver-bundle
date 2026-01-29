<?php

namespace Crtl\RequestDTOResolverBundle\Test\Unit\Exception;

use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationExceptionTest extends TestCase
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

    public function testExceptionMessage(): void
    {
        $exception = new RequestValidationException($this->object, $this->violations);

        $this->assertEquals('Error validating stdClass', $exception->getMessage());
    }

    public function testGetObject(): void
    {
        $exception = new RequestValidationException($this->object, $this->violations);

        $this->assertSame($this->object, $exception->getObject());
    }

    public function testGetViolations(): void
    {
        $exception = new RequestValidationException($this->object, $this->violations);
        $this->assertSame($this->violations, $exception->getViolations());
    }

    public function testCreateMethod(): void
    {
        $exception = RequestValidationException::create($this->object, $this->violations);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertInstanceOf(RequestValidationException::class, $exception);
        $this->assertSame($this->object, $exception->getObject());
        $this->assertSame($this->violations, $exception->getViolations());
        $this->assertEquals('Error validating stdClass', $exception->getMessage());
    }
}
