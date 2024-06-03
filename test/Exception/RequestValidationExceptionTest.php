<?php

namespace Crtl\RequestDTOResolverBundle\Test\Exception;

use Crtl\RequestDTOResolverBundle\Exception\RequestValidationException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationExceptionTest extends TestCase
{

    protected ConstraintViolationListInterface $violations;

    protected stdClass $object;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->object = new stdClass();
        $this->violations = $this->createMock(ConstraintViolationListInterface::class);
    }

    public function testExceptionMessage()
    {
        $exception = new RequestValidationException($this->object, $this->violations);

        $this->assertEquals("Error validating stdClass", $exception->getMessage());
    }

    public function testGetObject()
    {

        $exception = new RequestValidationException($this->object, $this->violations);

        $this->assertSame($this->object, $exception->getObject());
    }

    public function testGetViolations()
    {
        $exception = new RequestValidationException($this->object, $this->violations);
        $this->assertSame($this->violations, $exception->getViolations());
    }

    public function testCreateMethod()
    {

        $exception = RequestValidationException::create($this->object, $this->violations);

        $this->assertInstanceOf(RequestValidationException::class, $exception);
        $this->assertSame($this->object, $exception->getObject());
        $this->assertSame($this->violations, $exception->getViolations());
        $this->assertEquals("Error validating stdClass", $exception->getMessage());
    }


}