<?php

namespace Crtl\RequestDTOResolverBundle\Test\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\AbstractParam;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;

class TestClass
{
    public string $testProperty;
}

class TestParam extends AbstractParam
{
    public function getValueFromRequest(Request $request): mixed
    {
        return $request->request->get($this->getName());
    }
}

class AbstractParamTest extends TestCase
{
    public function testGetNameWithExplicitName()
    {
        $param = new TestParam("testName");

        $this->assertEquals("testName", $param->getName());
    }

    public function testGetNameWithPropertyName()
    {
        $param = new TestParam;

        $property = new ReflectionProperty(TestClass::class, "testProperty");
        $param->setProperty($property);

        $this->assertEquals("testProperty", $param->getName());
    }

    public function testGetNameThrowsLogicException()
    {
        $this->expectException(LogicException::class);

        $param = new TestParam;

        $param->getName();
    }

    public function testSetProperty()
    {
        $param = new TestParam;

        $property = new ReflectionProperty(TestClass::class, "testProperty");
        $param->setProperty($property);

        $this->assertSame($property, $param->getProperty());
    }

    public function testGetValueFromRequest()
    {
        $request = new Request([], ["param" => "value"]);

        $param = new TestParam("param");

        $this->assertEquals("value", $param->getValueFromRequest($request));
    }
}
